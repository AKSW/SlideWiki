<?php

class SlideWiki {
	private $dbLink;
	const slides_per_page = 20;
	const decks_per_page= 5;
	//maximum length of node titles displayed in the tree
	private $max_title_length = 30;
	
	public $user_id;
	
	function __construct() {
		$this->user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : -1;
		$this->db = new PDO ( DB_DSN, DB_USER, DB_PASSWORD );
		
		if (! $this->db) {
			die ( 'Could not connect: ' . mysql_error () );
		}
	}
	
	public function dbQuery($query, $params = array()) {
		$s = $this->db->prepare ( $query );
		if (! $s->execute ( $params )) {
			echo $query;
			print_r ( $s->errorInfo () );
		}
		return $s->fetchAll ();
	}
	public function dbGetRow($query, $params = array()) {
		return array_pop ( $this->dbQuery ( $query, $params ) );
	}
	public function dbGetCol($query, $params = array()) {
		$ret = array();
		foreach ( $this->dbQuery ( $query, $params ) as $val )
			$ret[] = array_pop ( $val );
		return $ret;
	}
	public function dbGetOne($query, $params = array()) {
		$results = $this->dbQuery ( $query, $params );
		if( $results ){
			return array_pop ( array_pop ( $results ) );
		}else{
			return null;
		}
	}
	public function dbInsert($table, $values) {
		$this->dbQuery ( 'INSERT INTO `' . $table . '` (`' . join ( '`,`', array_keys ( $values ) ) . '`) VALUES (:' . join ( ',:', array_keys ( $values ) ) . ')', $values );
		return $this->db->lastInsertId ();
	}
	public function dbUpdate($table, $values) {
		foreach ( $values as $key => $val )
			if ($key != 'id')
				$set [] = "`$key`=:$key";
		return $this->dbQuery ( 'UPDATE `' . $table . '` SET ' . join ( ',', $set ) . ' WHERE id=:id', $values );
	}
	/*************************** SEARCH STUFF *************************/
	
	//moved
	public function getItemParents($itemId,$itemType){		
		$result_array='';
		$id=$itemId;
		$type=$itemType;
		
		foreach (($this->dbQuery ("SELECT * FROM `deck_content` WHERE `item_type` = '".$type."' AND `item_id` = ".$id)) as $r){
					
				if (isset($r['deck_revision_id'])){
					$result_array = $result_array.'.'.$r['deck_revision_id'];
					$result_array = $result_array.'.'.$this->getItemParents($r['deck_revision_id'],'deck');
				}						
		};
		return $result_array;
		
	}
	//moved
	public function getItemChildren($deckId){		
		
		$id=$deckId;		
		$result_array='';
		foreach (($this->dbQuery ("SELECT * FROM `deck_content` WHERE `item_type` = 'deck' AND `deck_revision_id` = ".$id)) as $r){
					
				if (isset($r['item_id'])){
					$result_array = $result_array.'.'.$r['item_id'];
					$result_array = $result_array.'.'.$this->getItemChildren($r['item_id']);
				}						
		};
		return $result_array;
		
	}
	//compare parrents and children
	//moved
	public function parentsChildren($selectedId,$selectedType,$deckId) {
		$k=0;
		//form children array
		$children[] = $deckId;		
		$childrenArr = explode('.',$this->getItemChildren($deckId));		
		foreach ($childrenArr as $r){			
			if ($r>'')
				$children[] = $r;		
		}
        //form parents array	
		$parentsArr = explode('.',$this->getItemParents($selectedId,$selectedType));
		$parents=Array();
		if ($selectedType=='deck') 
			$parents[]=$selectedId;
		foreach ($parentsArr as $r){			
			if ($r>'')
				$parents[] = $r;		
		}
		//search for the same deckIds and return count of them
		foreach ($children as $child) {
			foreach ($parents as $parent){
				if ($child==$parent) $k++;
			}		
		}		
		return $k;
	}
	
	//moved
	public function removeLoopingDecks ($selectedId,$deckSet){
		
		$result_set=Array();
		$id_array = explode( '-',$selectedId);
		$id = $id_array[3];
		$selected_type = $id_array[2];		
		for($i = 0; $i < count ( $deckSet ); $i ++) {		
			$r = $deckSet [$i];			
				//check for endless loops
			if ($this->parentsChildren($id,$selected_type,$r['id'])==0) {
				$result_set[] = $r;
			}
			
		}
		return $result_set;
	}
	
	//moved
	public function pager ($order,$type,$total) {
		
		switch ($type) {
			case 'deck':
				$pages_count =  ceil($total/$this::decks_per_page);				
				break;
			case 'slide':
				$pages_count =  ceil($total/$this::slides_per_page);
				break;		
		}
		if ($pages_count>1) {
			echo '<div class="pager">';
			for ($i=1; $i<=$pages_count;$i++) {
				echo '<a href="javascript:submitSearch(\''.$type.'\',\''.$order.'\','.$i.')"><span class="pager">'.$i.'</span></a>';
			}
			echo '</div>';
		}
	}

	//moved
	public function deckBuilding($order, $deckSet,$page ) {
		$this->pager($order,'deck',count ( $deckSet ));
		$first = ($page-1)*$this::decks_per_page;		
		$k=0;		
		$deck = null;
		$decks = array();
		foreach($deckSet as $index => $deckRev){
			$deck = new Deck();
			$deck->id = $deckRev['id'];
			$deck->title = $deckRev['title'];
			
			$deck->revisionTime = $deckRev['timestamp'];
			
			$deck->abstract = $deckRev['abstract'];
			
			$decks[] = $deck;
		}
		echo '<ul class="deckOverviewList">';
		for($index=$first; $index<$first+$this::decks_per_page && $index<count($deckSet); $index++) {
			$r = $decks[$index];
			$i = 0;					
			$k++;			 
			echo '<li class="deckOverviewItem">';			
			$title=$r->title;			
			echo '<h3 class="deckOverviewTitle"><a href="./?url=main/deck&deck='.$r->id.'"';
			echo '<b>'.$k .'. </b>'; 								//numeration
			echo $r->title.'</a></h3>';
			echo '<div class="deck-menu deck-container">';
			foreach( $r->getSlides() as $slide ){
				$i++;
				if ($i <= 4) {
					echo '<div class="slide"><a href="./?url=main/deck&deck='.$deck->id.'#tree-'.$slide->deck.'-slide-'.$slide->id.'-'.$slide->position.'-view">';
					echo $slide->getThumbnailContent();
					echo '</a></div>';
				}
			}
			echo '<div class="addButton" style="display:none;"><input type="button" id="'.$r->id.'" name="add'.$r->id.'" value="Add" onclick="appendItemById(\''.$title.'\',this.id)"></div>';
			echo '</div>';			
			
		}
			
		echo '</ul>';
		
	}
	
	
	//moved
	public function slideBuilding($order,$slideSet,$page) {
		$this->pager($order,'slide',count ( $slideSet ));
		$position = 1;
		$prevSlide = $slideSet [0] ['slide'] - 1; //`slide` of the first element-1
		$k = 0; //counter for numeration
		$slidesToShow=array();
		echo '<ul class="deckOverviewList">';
		echo '<li class="deckOverviewItem">';
		echo '<div class="deck-menu deck-container" >';
		for($i = 0; $i < count ( $slideSet ); $i++) {			
			$r=$slideSet[$i];
						
			if ($r ['slide'] == $prevSlide)
				$position ++;
			else
				$position = 1;	
			
			if ($position == 1) {
				$slidesToShow[] = $r;
			}
		}
		$first = ($page-1)*$this::slides_per_page;
		for($index=$first; $index<$first+$this::slides_per_page && $index<count($slidesToShow); $index++)	
		{	
			$r=$slidesToShow[$index];
			$slide = new Slide();
			$slide->createFromID($r['id']);
			$slides[] = $slide;
		}
		foreach ($slides as $r)	 {
			$title=$r->getTitle();
			$deck_obj = $r->getLastDeck($r->id);
                        $deck_id = $deck_obj->id;
			$deck=new Deck();
			$deck->createFromID($deck_id);
			$k ++; //numeration	
			/*if (strlen($title)>10) {
				$title=substr($title,0,10).'...';
			}
			*/
			echo '<div class="slide"  style = "font-size:0.22em; height: 65px; padding: 5px 1%; margin: 0 1% 5px 0; position: relative;">';
			echo '<a href="./?url=main/deck&deck='.$deck->id.'#tree-'.$r->deck.'-slide-'.$r->id.'-'.$r->position.'-view">';
			echo $r->getThumbnailContent();
			echo '</a>';
			echo '</div>';
			echo '<div class="addButton" style="display:none; float:left; font-size:10pt"><input type="button" id="'.$r->id.'" name="add'.$r->id.'" value="Add" onclick="appendItemById(\''.$title.'\',this.id)"></div>';
				
			$prevSlide = $r->slide_id;
			
		}
			
		
		echo '</ul>';
		echo '</div>';
		
		
	}
	//moved
	public function searchDeck($keywords, $order) {
		$table = 'deck_revision';
		$field = 'title';
				
		return $this->dbQuery ( 'SELECT * FROM ' . $table . ' WHERE ' . $field . ' like "%' . $keywords . '%" ORDER BY `'.$order.'` DESC' );
	}
	//moved
	public function searchSlide($keywords, $order) {
		$table = 'slide_revision';
		$field = 'content';
		if ($order=='title') $order_db='content'; else $order_db=$order;
		
		return $this->dbQuery ( 'SELECT * FROM ' . $table . ' WHERE ' . $field . ' like "%' . $keywords . '%" ORDER BY `slide` ASC, `'.$order_db.'` DESC' );
	
	}
	//moved
	public function searchItem($selectedId,$typeOfSearch,$keywords, $order, $page) {
		
		switch ($typeOfSearch) {
			case 'deck' :
				$deckSet = $this->searchDeck ( $keywords, $order );
				if ($selectedId!='0') {
					
					$deckSet = $this->removeLoopingDecks($selectedId,$deckSet);
				}				
				return $this->deckBuilding ($order,$deckSet,$page );
				break;
			case 'slide' :
				$slideSet = $this->searchSlide ( $keywords, $order );
				return $this->slideBuilding ($order, $slideSet,$page );
				break;
			default :
				break;
		}
	}
	
	//moved
	public function searchDeckByTag($tag, $keywords, $order) {
		
		return $this->dbQuery ( '
		
		SELECT * FROM deck_revision WHERE id IN
			(SELECT id FROM deck_revision WHERE id IN
				(SELECT item_id FROM tag WHERE tag = "'.$tag.'")) AND `title` like "%' . $keywords . '%" 
		ORDER BY `'.$order.'` DESC'  );	
	}
	//moved
	public function searchByTag($tag, $keywords, $order , $page) {
		$deckSet = $this->searchDeckByTag($tag,$keywords, $order);
		return $this->deckBuilding ( $order,$deckSet,$page );
	}
	//Search with 'match' function for non-one-word querys
	//moved
	public function searchMatchDeck($keywords, $order) {
		$table = 'deck_revision';
		$field = 'title';
		
		return $this->dbQuery ( 'SELECT * FROM ' . $table . ' WHERE MATCH (' . $field . ') AGAINST ("' . $keywords . '") ORDER BY `'.$order.'` DESC' );
	}
	//moved
	public function searchMatchSlide($keywords, $order) {
		$table = 'slide_revision';
		$field = 'content';
		if ($order=='title') $order_db='content'; else $order_db=$order;
		return $this->dbQuery ( 'SELECT * FROM ' . $table . ' WHERE MATCH (' . $field . ') AGAINST ("' . $keywords . '") ORDER BY `slide` ASC, `'.$order_db.'` DESC' );
	}
	//moved
	public function searchMatchItem($selectedId,$typeOfSearch,$keywords, $order, $page) {
		
		switch ($typeOfSearch) {
			case 'deck' :		
				
				$deckSet = $this->searchMatchDeck ( $keywords, $order );
				if ($selectedId!='0') {
					
					$deckSet = $this->removeLoopingDecks($selectedId,$deckSet);
				}				
				return $this->deckBuilding ( $order,$deckSet,$page );
				break;
			case 'slide' :
				$slideSet = $this->searchMatchSlide ( $keywords , $order);
				return $this->slideBuilding ( $order, $slideSet, $page );
				break;
			default :
				break;
		}
	}
	//moved
	public function searchMatchDeckByTag($tag, $keywords, $order) {
		
		return $this->dbQuery ( '
		
		SELECT * FROM deck_revision WHERE id IN
			(SELECT id FROM deck_revision WHERE id IN
				(SELECT item_id FROM tag WHERE tag = "'.$tag.'")) AND MATCH (`title`) AGAINST ("' . $keywords . '")
		ORDER BY `'.$order.'` DESC' );
	}
	//moved
	public function searchMatchByTag($tag, $keywords, $order, $page) {
		$deckSet = $this->searchMatchDeckByTag($tag,$keywords, $order);
		return $this->deckBuilding ($order,$deckSet,$page );
	}
	
	//migrated to new model
	public function getTags($type, $id) {
		return $this->dbGetCol ( 'SELECT tag FROM tag WHERE item_type=:type AND item_id=:id', array (type => $type, id => $id ) );
	}
	//migrated to new model
	public function delTags($type, $id) {
		return $this->dbQuery ( 'DELETE FROM tag WHERE item_type=:type AND item_id=:id', array (type => $type, id => $id ) );
	}
	//migrated to new model
	public function addTags($type, $id, $tags) {
		foreach ( explode ( ' ', $tags ) as $tag )
			$this->dbInsert ( 'tag', array (tag => $tag, item_type => $type, item_id => $id ) );
	}
	//migrated to new model
	public function setTags($type, $id, $tags) {
		$this->delTags ( $type, $id );
		$this->addTags ( $type, $id, $tags );
	}
	
	/*************************** DECKS STUFF **************************/
	//migrated to new model
	public function getAllDecks() {
		return $this->dbQuery ( 'SELECT * FROM deck_revision ORDER BY title DESC LIMIT 5' );
	}
	//migrated to new model
	public function showDeckPreview($id) {
		return $this->showDeckContent ( $id, 1 );
	}
	//migrated to new model
	public function getDeckByID($id) {
	
	}
	//migrated to new model
	public function editDeck($id) {
		$deck = $this->dbGetRow ( 'SELECT * FROM deck_revision WHERE id=:id', array ('id' => $id ) );
		$tags = join ( ' ', $this->getTags ( 'deck', $id ) );
		$theme = '';
		foreach ( $this->getAllStyles () as $r ) {
			$themeId = isset($deck ['theme']) ? $deck ['theme'] : -1;
			$theme .= '<option value="' . $r ['id'] . '" ' . ($themeId == $r ['id'] ? 'selected' : '') . '>' . $r ['name'] . '</option>';
		}
		return '<form id="editdeck"><label>Title:<input type="text" name="deck[title]" value="' . $deck ['title'] . '"></label>
			<label>Abstract:<br /><textarea name="deck[abstract]">' . $deck ['abstract'] . '</textarea></label>
			<label>Tags:<input type="text" name="deck[tags]" value="' . $tags . '"></label>
			<label>Default theme:<br /><select name="deck[theme]">' . $theme . '</select><br />
			<p id="notice"></p>
			<input type="button" onclick="$(\'#notice\').load(\'backend/ajax.php?f=saveDeck&id=' . $id . '&\'+$(\'#editdeck\').serialize());" value="Submit" /></form>';
	}
	//migrated to new Model
	public function saveDeck($id, $meta) {
		$this->setTags ( 'deck', $id, $meta ['tags'] );
		unset ( $meta ['tags'] );
		$meta ['id'] = $id;
		$this->dbUpdate ( 'deck_revision', $meta );
		return "Deck metadata was sucessfully saved!";
	}
	//migrated to new model
	public function getDeckContent($deck, $limit = NULL) {
		return $this->dbQuery ( 'SELECT deck_revision_id, item_type, item_id, position FROM deck_content WHERE deck_revision_id=' . $deck . ' ORDER BY position' . ($limit ? ' LIMIT ' . $limit : '') );
	}
	//migrated to new Model
	public function showDeckContent($deck, $limit = NULL) {
		$ret = '';
		foreach ( $this->getDeckContent ( $deck, $limit ) as $item ) {
			if ($item ['item_type'] == 'deck')
				$ret .= $this->showDeckContent ( $item ['item_id'], $limit );
			else {
				$node_id = 'tree-' . $item ['deck_revision_id'] . '-slide-' . $item ['item_id'] . '-' . $item ['position'];
				$s = $this->getSlide ( $item ['item_id'] );
				$slides [$item ['item_id']] = array ('note' => $s ['note'], 'license' => $s ['license'], 'contributors' => $this->getSlideContributors ( $item ['item_id'] ), 'follow' => $this->isFollowing ( 'slide', $item ['item_id'] ) );
				$ret .= '<div id="' . $node_id . '-view" class="slide" onclick="selectNode(\'' . $node_id . '\')"><a href="main.php?deck=' . $deck . '#' . $node_id . '-view">' . $s ['content'] . '</a></div>';
			}
		}
		return $ret . '<script>$.extend(slides,' . json_encode ( $slides ) . ');</script>';
	}
	//migrated to new Model
	public function playDeckContent($deck) {
		$ret = null;
		foreach ( $this->getDeckContent ( $deck ) as $item ) {
			if ($item ['item_type'] == 'deck')
				$ret .= $this->playDeckContent ( $item ['item_id'] );
			else {
				$node_id = 'tree-' . $item ['deck_revision_id'] . '-slide-' . $item ['item_id'] . '-' . $item ['position'];
				$s = $this->getSlide ( $item ['item_id'] );
				$slides [$item ['item_id']] = array ('note' => $s ['note'], 'license' => $s ['license'], 'contributors' => $this->getSlideContributors ( $item ['item_id'] ), 'follow' => $this->isFollowing ( 'slide', $item ['item_id'] ) );
				$ret .= '<div id="' . $node_id . '-view" class="slide">' . $s ['content'] . '</div>';
			}
		}
		return $ret . '<script>$.extend(slides,' . json_encode ( $slides ) . ');</script>';
	}
	//migrated to new model
	public function createDeck($title, $comment = NULL) {
		$deck = $this->dbInsert ( 'deck', array ('user_id' => $this->user_id ) );
		return $this->dbInsert ( 'deck_revision', array ('deck_id' => $deck, 'user_id' => $this->user_id, 'title' => $title, 'comment' => $comment ) );
	}
	public function deleteDeckFromPosition($container_deck, $position) {
		$this->deleteItemFromPosition ( $container_deck, $position );
	}
	public function insertSlideToDeck($slide, $deck, $after = NULL) {
		return $this->insertItemToDeck ( $slide, 'slide', $deck, $after );
	}
	public function insertNewSlideToDeck($title, $deck, $after = NULL, $comment = NULL) {
		$slide = $this->createSlide ( $title, $comment );
		$this->insertItemToDeck ( $slide, 'slide', $deck, $after );
		return $slide;
	}
	public function insertNewDeckToDeck($title, $deck, $after = NULL) {
		$deckToAppend = $this->createDeck ( $title );
		$this->insertItemToDeck ( $deckToAppend, 'deck', $deck, $after );
		return $deckToAppend;
	}
	public function insertDeckToDeck($deckToAppend, $deck, $after = NULL) {
		return $this->insertItemToDeck ( $deckToAppend, 'deck', $deck, $after );
	}
	public function insertNewItemToDeck($type, $deck, $title = 'New Title', $after = NULL) {
		if ($type == "slide")
			return $this->insertNewSlideToDeck ( $title, $deck, $after );
		else
			return $this->insertNewDeckToDeck ( $title, $deck, $after );
	}
	public function insertItemToDeck($item, $type, $deck, $after = NULL) {
		if ($after && $after!='-1')
			$this->dbQuery ( 'UPDATE deck_content SET position=position+1 WHERE deck_revision_id=' . $deck . ' AND position>' . $after . ' ORDER BY position DESC' );
		else if($after=='-1') {		
			$this->dbQuery ( 'UPDATE deck_content SET position=position+1 WHERE deck_revision_id=' . $deck );
			$after=0;
		} else
			$after = $this->dbGetOne ( 'SELECT MAX(position) FROM deck_content WHERE deck_revision_id=' . $deck );
		return $this->dbInsert ( 'deck_content', array ('deck_revision_id' => $deck, 'item_type' => $type, 'item_id' => $item, 'position' => $after + 1 ) );
	}
	
	public function getLastRevision($deck) {
		return $this->dbGetOne ( 'SELECT id FROM deck_revision WHERE deck_id=' . $deck . ' ORDER BY timestamp DESC LIMIT 1' );
	}
	
	public function getLastRevisionTimestamp($deck) {
		return $this->dbGetOne ( 'SELECT timestamp FROM deck_revision WHERE deck_id=' . $deck . ' ORDER BY timestamp DESC LIMIT 1' );
	}
	
	public function getLastRevisionUserID($deck) {
		return $this->dbGetOne ( 'SELECT user_id FROM deck_revision WHERE deck_id=' . $deck . ' ORDER BY timestamp DESC LIMIT 1' );
	}
	
	public function getCreationTime($deck) {
		return $this->dbGetOne ( 'SELECT timestamp FROM deck WHERE id=' . $deck . '
		ORDER BY id DESC LIMIT 1' );
	}
	//migrated to new model
	public function copyDeck($deck) {
		$this->dbQuery ( 'INSERT INTO deck SELECT NULL,NULL,' . $this->user_id . ' FROM deck WHERE id=' . $deck );
		$newDeck = $this->db->lastInsertId ();
		$this->copyRevision ( $this->getLastRevision ( $deck ) );
		return $newDeck;
	}
	//migrated to new model
	public function copyRevision($revision, $deck = NULL, $comment) {
		$this->dbQuery ( 'INSERT INTO deck_revision SELECT NULL,' . ($deck ? $deck : 'deck_id') . ',NULL,' . $this->user_id . ',id,"' . ($comment ? $comment : 'Deck copied') . '",abstract,license FROM deck_revision WHERE id=' . $revision );
		$newRevision = $this->db->lastInsertId ();
		$this->dbQuery ( 'INSERT INTO deck_content SELECT ' . $newRevision . ',item_type,item_id,position FROM deck_content WHERE deck_revision_id=' . $revision );
		return $newRevision;
	}
	//migrated to new model
	public function getDeckTitle($deck) {
		return $this->dbGetOne ( 'SELECT title FROM deck_revision WHERE deck_revision.id=' . $deck );
	}
	public function setDeckTitle($deck, $title) {
		return $this->dbGetOne ( 'UPDATE deck_revision SET title="' . $title . '" WHERE id=' . $deck );
	}
	public function deleteDeckChildren($deck) {
		//TODO: delete all the sub nodes recursively
		$this->dbQuery ( 'DELETE FROM deck_content WHERE deck_revision_id=' . $deck );
	}
	//migrated to new model
	//TODO: it only works within one deck, if same slides are in different decks we need to recursively update them
	public function replaceSlide($deck, $slide, $content, $comment = NULL, $note = NULL, $position= NULL) {
		$rev = $this->commitSlide ( $this->getSlideForRevision ( $slide ), stripslashes ( $content ), $comment, $slide, $note );
		if($position)
			$this->dbQuery ( 'UPDATE deck_content SET item_id=' . $rev . ' WHERE deck_revision_id=' . $deck . ' AND item_id=' . $slide . ' AND item_type<> "deck" AND position='.$position );
		else
			$this->dbQuery ( 'UPDATE deck_content SET item_id=' . $rev . ' WHERE deck_revision_id=' . $deck . ' AND item_id=' . $slide . ' AND item_type<> "deck"' );
		return $rev;
	}
	public function moveItem($item, $type, $new_deck, $old_deck, $old_position, $after) {
		$this->deleteItemFromPosition ( $old_deck, $old_position );
		$this->insertItemToDeck ( $item, $type, $new_deck, $after );
	}
	public function createItem($type, $title) {
		if ($type == "slide")
			return $this->createDeck ( $title );
		else
			return $this->createSlide ( $title );
	
	}
	//migrated to new model
	public function showDeckRevisions($deck) {	
		$ret = '';
		foreach ( $this->dbQuery ( 'SELECT deck_revision.id, timestamp, user_id, email FROM deck_revision INNER JOIN users ON(user_id=users.id) WHERE deck_id=' . $deck . ' ORDER BY timestamp DESC' ) as $row )
			$ret .= '<li>' . ($row ['id'] == $deck ? '<b>' . $row ['timestamp'] . '</b>' : $row ['timestamp']) . ' by <a href="user.php?id=' . $row ['user_id'] . '">' . $row ['email'] . '</a></li>';
		return '<ul>' . $ret . '</ul>';
	}
	public function showDeckUsage($revision) {
		$ret = '';
		foreach ( $this->dbQuery ( 'SELECT deck_revision.id, title, deck_revision.user_id, email
			FROM deck_content INNER JOIN deck_revision ON(deck_revision_id=deck_revision.id) INNER JOIN users ON(deck_revision.user_id=users.id)
			WHERE item_type="deck" AND item_id=' . $revision . ' GROUP BY deck_revision.id ORDER BY deck_revision.timestamp DESC' ) as $row )
			$ret .= '<li><a href="main.php?deck=' . $row ['id'] . '">' . ($row ['id'] == $revision ? '<b>' . $row ['title'] . '</b>' : $row ['title']) . '</a> by <a href="user.php?id=' . $row ['user_id'] . '">' . $row ['email'] . '</a></li>';
		return '<ul>' . $ret . '</ul>';
	}
	//migrated to new model 
	//gets a root deck and searches for identical slide/decks in it.
	//returns the array of identical items
	public function getIdenticalItems($container_deck,$type,$id) {
		if($type=="slide")
			return $this->getIdenticalSlides($container_deck,$id);
		else 
			return $this->getIdenticalDecks($container_deck,$id);
	}	
	public function countIdenticalItems($container_deck,$type,$id) {
		return count($this->getIdenticalItems($container_deck,$type,$id));
	}	
	public function getIdenticalSlides($container_deck,$id) {
		$output = $childs = array ();
		foreach ( $this->getDeckContent ( $container_deck ) as $v ) {
			if ($v ['item_type'] == 'deck')
					$childs = $this->getIdenticalSlides($v ['item_id'],$id);
			if(count($childs))
					$output [] =$childs;
			if ($v ['item_type']=='slide' && $v ['item_id']==$id)
				$output [] = array ('parent' => $v ['deck_revision_id'],'position' => $v ['position'] );
			$childs = array ();
		}
		return $output;
	}	
	public function getIdenticalDecks($container_deck,$id) {
		return 0;
	}	
	/*************************** SLIDES STUFF **************************/
	
	public function getSlide($id) {
		return $this->dbGetRow ( "SELECT * FROM slide_revision WHERE id=:id", array ('id' => $id ) );
	}
	//migrated to new model
	public function getSlideForRevision($revision) {
		return $this->dbGetOne ( 'SELECT slide FROM slide_revision WHERE id=' . $revision );
	}
	//migrated to new model
	public function createSlide($content = NULL, $comment = NULL) {
		$slide = $this->dbInsert ( 'slide', array ('user_id' => $this->user_id ) );
		if ($content)
			return $this->commitSlide ( $slide, $content, $comment );
		return $slide;
	}
	//migrated to new model
	public function commitSlide($slide, $content, $comment = NULL, $based_on = NULL, $note = NULL) {
		return $this->dbInsert ( 'slide_revision', array ('slide' => $slide, 'content' => $content, 'user_id' => $this->user_id, 'comment' => $comment, 'based_on' => $based_on, 'note' => $note ) );
	}
	//migarted to new model
	public function changeSlideAtPosition($container_deck, $newslide, $position) {
		$this->dbQuery ( 'UPDATE  deck_content SET item_id=' . $newslide . ' WHERE deck_revision_id=' . $container_deck . ' AND position=' . $position );
	}
	//migarted to new model
	public function deleteItemFromPosition($container_deck, $position) {
		$this->dbQuery ( 'DELETE FROM deck_content WHERE deck_revision_id=' . $container_deck . ' AND position=' . $position );
		$this->dbQuery ( 'UPDATE  deck_content SET position=position-1 WHERE deck_revision_id=' . $container_deck . ' AND position>' . $position );
	}
	//migrated to new model
	public function getSlideTitle($slide) {
		preg_match ( "/<h2>(.*?)<\/h2>/", $this->getSlideContent ( $slide ), $matches );
		if (count ( $matches ))
			return strip_tags ( $matches [0] );
		else
			return "Untitled";
	}
	//migrated to new model
	public function setSlideTitle($slide, $title) {
		$content = $this->getSlideContent ( $slide );
		$content = preg_replace ( "/<h2>(.*?)<\/h2>/", "<h2>" . $title . "</h2>", $content, 1, $count );
		if (! $count) {
			$content = "<h2>" . $title . "</h2>" . $content;
		}
		$this->dbQuery ( 'UPDATE slide_revision SET content="' . $content . '",comment="Title changed to: ' . $title . '" WHERE id=' . $slide );
	}
	public function getSlideContent($slide) {
		return $this->dbGetOne ( 'SELECT content FROM slide_revision WHERE id=' . $slide );
	}
	public function getSlideNote($slide) {
		return $this->dbGetOne ( 'SELECT note FROM slide_revision WHERE id=' . $slide );
	}
	public function getLastRevisionSlideContent($slide) {
		return $this->dbGetOne ( 'SELECT content FROM slide_revision WHERE slide=' . $slide . ' ORDER BY timestamp DESC LIMIT 1' );
	}
	public function getLastRevisionSlideRevisionID($slide) {
		return $this->dbGetOne ( 'SELECT id FROM slide_revision WHERE slide=' . $slide . ' ORDER BY timestamp DESC LIMIT 1' );
	}
	public function getLastRevisionSlideTimestamp($slide) {
		return $this->dbGetOne ( 'SELECT timestamp FROM slide_revision WHERE slide=' . $slide . ' ORDER BY timestamp DESC LIMIT 1' );
	}
	//migrated to new model
	public function showSlideRevisions($revision) {
		$slide = $this->getSlideForRevision ( $revision );
		$ret = '';
		foreach ( $this->dbQuery ( 'SELECT slide_revision.id, timestamp, content, comment, user_id, username FROM slide_revision INNER JOIN users ON(user_id=users.id) WHERE slide=' . $slide . ' ORDER BY timestamp DESC' ) as $row )
			$ret .= '<li style="clear:both">' . ($row ['id'] == $revision ? '<b>' . $row ['timestamp'] . '</b>' : $row ['timestamp']) . ' by <a href="user.php?id=' . $row ['user_id'] . '">' . $row ['username'] . '</a><br />
				<div class="sliderevision deck-menu"><div class="slide">' . $this->getSlideContent ( $row ['id'] ) . '</div></div><div>' . $row ['comment'] . '</div><br style="clear:both" /><a href="#" onclick="replaceSlideWith(\'' . $row ['id'] . '\')">Use this revision</a>
				</li>';
		return '<ul>' . $ret . '</ul><script>$.deck(\'#slideviewrevision > #slide\');</script><br style="clear:both" />';
	}
	//migrated to new model
	public function showSlideUsage($revision) {
		$ret = '';
		foreach ( $this->dbQuery ( 'SELECT deck_revision.id, title, deck_revision.user_id, email
			FROM deck_content INNER JOIN deck_revision ON(deck_revision_id=deck_revision.id) INNER JOIN users ON(deck_revision.user_id=users.id)
			WHERE item_type="slide" AND item_id=' . $revision . ' GROUP BY deck_revision.id ORDER BY deck_revision.timestamp DESC' ) as $row )
			$ret .= '<li><a href="main.php?deck=' . $row ['id'] . '">' . ($row ['id'] == $revision ? '<b>' . $row ['title'] . '</b>' : $row ['title']) . '</a> by <a href="user.php?id=' . $row ['user_id'] . '">' . $row ['email'] . '</a></li>';
		return '<ul>' . $ret . '</ul>';
	}
	//migrated to new Slide Model
	public function getSlideContributors($id) {
		$c = $this->dbGetRow ( 'SELECT username,user_id,based_on FROM slide_revision INNER JOIN users ON(user_id=users.id) WHERE slide_revision.id=:id', array ('id' => $id ) );
		if ($c['based_on'])
			return array_unique ( array_merge ( $this->getSlideContributors ( $c['based_on'] ), array ('=' . $c['user_id'] => $c['username'] ) ) );
		return array ('=' . $c['user_id'] => $c['username'] );
	}
	
	/*************************** DISCUSSION **************************/
	//migrated to new Slide Model
	public function showDiscussion($type, $id) {
		if (! $id)
			return;
		
		$ret = '';
		#$ret='SELECT c.id,title,c.timestamp,text,username FROM comment c INNER JOIN users u ON(user_id=users.id) WHERE item_type="'.$type.'" AND item_id='.$id.' ORDER BY timestamp DESC';
		$res = $this->dbQuery ( 'SELECT c.id,title,c.timestamp,text FROM comment c INNER JOIN users u ON(user_id=u.id) WHERE item_type="' . $type . '" AND item_id=' . $id . ' ORDER BY timestamp DESC' );
		foreach ( $res as $row )
			$ret .= '<div class="comment" style="margin:20px; padding:5px; border:solid black 1px;"><div class="title">' . $row [username] . ' (' . $row [timestamp] . '): <b>' . $row [title] . '</b></div>' . $row [text] . $this->showDiscussion ( 'comment', $row [id] ) . '</div>';
		return $ret . '<a href="#comment" onclick="addComment($(this),\'' . $type . '\',' . $id . ')">Add ' . ($type == 'comment' ? 'reply' : 'comment') . '</a>';
	}
	//migrated to new Slide Model
	public function saveComment($type, $id, $title, $text) {
		$this->dbQuery ( 'INSERT INTO comment VALUES (NULL,' . $this->user_id . ',"' . $type . '","' . $id . '","' . $title . '","' . $text . '",NULL)' );
		return '<div class="comment"><div class="title">' . $title . '</div>' . $text . '</div>';
	}
	/*************************** TREE STUFF **************************/
	//migrated to new model
	private function getTreeNodes($deck) {
		$output = $childs = array ();
		foreach ( $this->getDeckContent ( $deck ) as $v ) {
			if ($v ['item_type'] == 'deck')
				$childs = $this->getTreeNodes ( $v ['item_id'] );
			$output [] = array (
				'data' => array (
					"title" => $this->shortenTitle ( ($v ['item_type'] == 'slide' ? $this->getSlideTitle ( $v ['item_id'] ) : $this->getDeckTitle ( $v ['item_id'] )) ), 
					'icon' => ($v ['item_type'] == 'slide' ? 'static/img/file.png' : 'static/img/folder.png'), 
					'attr' => array ('class' => $v ['item_type'] . '-' . $v ['item_id'], 
						'id' => 'tree-' . $deck . '-' . $v ['item_type'] . '-' . $v ['item_id'] . '-' . $v ['position'], 
						'href' => '#tree-' . $deck . '-' . $v ['item_type'] . '-' . $v ['item_id'] . '-' . $v ['position'] . '-view' 
					) 
				)//'state' => ($v ['item_type'] == 'slide' ? 'closed' : 'open')
				, 'children' => $childs );
			$childs = array ();
		}
		return $output;
	}
	//migrated to new model
	private function getTree($deck) {
		$output [] = array (
			'data' => array (
				"title" => $this->shortenTitle ( $this->getDeckTitle ( $deck ) ), 
				'icon' => 'static/img/folder.png', 
				'attr' => array (
					'class' => 'deck-' . $deck, 
					'id' => 'tree-0-deck-' . $deck . '-1', 
					'href' => '#tree-0-deck-' . $deck . '-1-view' 
				) 
			), 
			'children' => $this->getTreeNodes ( $deck ) );
		return $output;
	}
	//migrated to new model
	public function getTreeJSON($deck) {
		$output = $this->getTree ( $deck );
		return json_encode ( $output );
	}
	//migrated to new model
	public function shortenTitle($title) {
		if (strlen ( $title ) > $this->max_title_length) {
			return substr ( trim ( $title ), 0, $this->max_title_length ) . '...';
		} else {
			return $title;
		}
	}
	/*************************** USERS STUFF **************************/
	public function authUser($user, $pass) {
		return $this->dbGetOne ( 'SELECT id FROM users WHERE email="' . $user . '" AND password="' . $pass . '"' );
	}
	
	public function getUserData($uid) {
		return $this->dbQuery ( 'SELECT username, email FROM users WHERE id=' . $uid . ' LIMIT 1' );
	}
	
	public function createUser($email, $username, $pass) {
		$uid = $this->dbGetOne ( 'SELECT id FROM users WHERE email="' . $email . '" OR username="' . $username . '" LIMIT 1' );
		if (isset ( $uid )) {
			echo "Error: user or email exists";
			return "Error: user or email exists";
		}
		$this->dbInsert ( 'users', array (email => $email, username => $username, password => $pass ) );
		$uid = $this->db->lastInsertId ();
		return $uid;
	}
	//migrated to new Model
	public function isFollowing($item_type, $item_id) {
		if (! $this->user_id)
			return;
		if ($this->dbGetOne ( 'SELECT user_id FROM subscription WHERE user_id=:user_id AND item_type=:item_type AND item_id=:item_id', array ('user_id' => $this->user_id, 'item_type' => $item_type, 'item_id' => $item_id ) ))
			return '<span onclick="$(this).parent().load(\'backend/ajax.php?f=unFollow&item_type=' . $item_type . '&id=' . $item_id . '\')">Following</span>';
		else
			return '<span onclick="$(this).parent().load(\'backend/ajax.php?f=follow&item_type=' . $item_type . '&id=' . $item_id . '\')">Follow</span>';
	}
	public function follow($item_type, $item_id) {
		$this->dbInsert ( 'subscription', array ('user_id' => $this->user_id, 'item_type' => $item_type, 'item_id' => $item_id ) );
		return $this->isFollowing ( $item_type, $item_id );
	}
	public function unFollow($item_type, $item_id) {
		$this->dbQuery ( 'DELETE FROM subscription WHERE user_id=:user_id AND item_type=:item_type AND item_id=:item_id', array ('user_id' => $this->user_id, 'item_type' => $item_type, 'item_id' => $item_id ) );
		return $this->isFollowing ( $item_type, $item_id );
	}
	public function getPreference($preference) {
		return $this->dbGetOne ( 'SELECT value FROM preference WHERE user_id=:user_id AND preference=:preference', array ('user_id' => $this->user_id, 'preference' => $preference ) );
	}
	public function setPreference($preference, $value) {
		$this->dbQuery ( 'DELETE FROM preference WHERE user_id=:user_id AND preference=:preference', array ('user_id' => $this->user_id, 'preference' => $preference ) );
		return $this->dbQuery ( 'INSERT INTO preference VALUES (:user_id,:preference,:value)', array ('user_id' => $this->user_id, 'preference' => $preference, 'value' => $value ) );
	}
	
	/*************************** STYLES STUFF **************************/
	public function addStyle($name, $css) {
		$this->dbInsert ( 'style', array ('user_id' => $this->user_id, 'name' => $name, 'css' => $css ) );
	}
	//migrated to new model
	public function getAllStyles() {
		return $this->dbQuery ( 'SELECT id,name FROM style ORDER BY name' );
	}
	public function getCSS($id) {
		header ( 'Content-Type: text/css' );
		return $this->dbGetOne ( 'SELECT css FROM style WHERE id=' . $id );
	}
	public function getStyle($id) {
		return $this->dbGetRow ( 'SELECT * FROM style WHERE id=' . $id );
	}
	
	/*************************** MEDIA STUFF *************************/
	public function createImage($uri, $type = 'img') {
		$image = $this->dbInsert ( 'media', array ('user_id' => $this->user_id, 'type' => $type, 'uri' => $uri ) );
		return $image;
	}

}

?>
