<?php

// simple deck class
class DeckList extends Model {
	public $decks;
	const decks_per_page = 10;
	const max_page_links = 10;
        public $pager_code;
        public $languages = array();
        public $current;
        public $own = false;
	function __construct(){
		$decks = array();
		
		// connect to db
		if( $this->connect(DB_DSN, DB_USER, DB_PASSWORD) == 0 )
			die ("Could not connect to db");
	}
		
	function getAllDecks($limit = 30,$exceptFeatured=0){
		// query here
		if($exceptFeatured)
			$res = $this->dbQuery( 'SELECT * FROM deck_revision WHERE visibility=1 AND is_featured=0 ORDER BY timestamp DESC LIMIT '.$limit );
		else
			$res = $this->dbQuery( 'SELECT * FROM deck_revision WHERE visibility=1 ORDER BY timestamp DESC LIMIT '.$limit );
		$deck = null;
		$decks = array();
		foreach($res as $index => $deckRev){
			$deck = new Deck();
			$deck->id = $deckRev['id'];
			$deck->title = $deckRev['title'];
			$deck->slug_title = $deck->sluggify($deck->title);
			$deck->revisionTime = $deckRev['timestamp'];
			
			$deck->abstract = $deckRev['abstract'];
			
			$decks[] = $deck;
		}
		
		return $decks;
	}
	
	function getAllPopular($limit = 30)	{
	
		$res = $this->dbQuery( 'SELECT * FROM deck_revision WHERE visibility=1 ORDER BY popularity DESC LIMIT '.$limit );
		
		$deck = null;
		$decks = array();
		foreach($res as $index => $deckRev){
			$deck = new Deck();
			$deck->id = $deckRev['id'];
			$deck->title =  $deckRev['title'];
			
			$deck->revisionTime = $deckRev['timestamp'];
			
			$deck->abstract =  $deckRev['abstract'];
			
			$decks[] = $deck;
		}
		
		return $decks;
	}
	function getAllFeatured($limit = 30)	{
	
		$res = $this->dbQuery( 'SELECT * FROM deck_revision WHERE is_featured=1 AND visibility=1 ORDER BY priority ASC LIMIT '.$limit );
		
		$deck = null;
		$decks = array();
		foreach($res as $index => $deckRev){
			$deck = new Deck();
			$deck->id = $deckRev['id'];
			$deck->title =  $deckRev['title'];
			$deck->slug_title = $deck->sluggify($deck->title);
			$deck->revisionTime = $deckRev['timestamp'];
			
			$deck->abstract =  $deckRev['abstract'];
			
			$decks[] = $deck;
		}
		
		return $decks;
	}	
	public function pager ($total, $page) {		
		$first = 1;			
		$max_count=$this::max_page_links;
                $pages_count =  ceil($total/$this::decks_per_page);
                if ($this->own){
                    $function = 'submitSearchOwn';
                }else{
                    $function = 'submitSearch';
                }
		if ($pages_count>1) {
			$this->pager_code.= '<div class="pager">';
			if ($page>$max_count) {
				$first = $page;
				$pre_first = $first - 1;
				$this->pager_code.= '<a href="javascript:'.$function.'(\'deck\',\'1\')"><span class="pager"><<</span></a>';				
                        }
			if ($page > $max_count + 1) {
                            $this->pager_code.= '<a href="javascript:'.$function.'(\'deck\','.$pre_first.')"><span class="pager"><</span></a>';
                        }
			$last = $first + $max_count - 1;
			$pos_last = $last+1;
			for ($i = $first; $i <= $pages_count && $i <= $last; $i++) {
				$class='pager';
				if ($i==$page) $class .= ' current';
                                $this->pager_code .= '<a href="javascript:'.$function.'(\'deck\','.$i.')"><span class="'.$class.'">'.$i.'</span></a>';
                        }
			if ($max_count + $first - 2 < $pages_count) {
                            $this->pager_code .= '<a href="javascript:'.$function.'(\'deck\','.$pos_last.')"><span class="pager">></span></a>';
                        }
			if ($max_count + $first - 1 < $pages_count) {
                            $this->pager_code .= '<a href="javascript:'.$function.'(\'deck\','.$pages_count.')"><span class="pager">>></span></a>';
                        }
			$this->pager_code.= '</div>';
		}
	}
        public function searchMatchAll($keywords, $order){
            $query='';
            switch ($order) {			
                case 'date' : $order_db='`timestamp` DESC '; break;
                case 'title' : $order_db='`title` ASC '; break;
                case 'popularity' : $order_db='`popularity` DESC '; break;
                default: $order_db='`timestamp` DESC ';		
            }
            $cond1 = "";
            $cond11 = "";	
            if($order=='featured'){
                $cond1=" AND is_featured=1 ";
                $cond11=" WHERE is_featured=1 ";	
            }         
            if (strlen($keywords)>2) {
               	$query =  'SELECT * FROM deck_revision WHERE MATCH (title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) 
                        '.$cond1.'GROUP BY `deck_id` ORDER BY ' . $order_db  ;
            }else{
                $query = 'SELECT * FROM deck_revision '.$cond11.' GROUP BY `deck_id` ORDER BY ' . $order_db  ;
            }
            return $this->dbQuery ($query);
        }
        public function buildWithQuestions($keywords, $order){
            $all_decks = array();
            $all_decks = $this->searchMatchAll($keywords, $order);
            $result = array();
            foreach($all_decks as $deck){
                $deck_obj = new Deck();
                $deck_obj->id = $deck['id'];
                $deck_obj->deck_id = $deck['deck_id'];
                $id = $deck_obj->getLastRevisionID();                
                $test = new Test();                
                $test->createFromItem($id);
                if (($test->quest_count)>0){
                    $test->id = $id;
                    $result [] = $test;
                }
            }
            return $result;
        }
        
        public function createLanguageList($keywords, $tag, $current = false, $user_id = false){
            $languages = array();
            if (strlen($keywords) > 2){
                if ($tag == 'all'){
                    if ($this->own){
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=\''.$user_id.'\' AND MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `deck_id` ');
                    }else{
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `deck_id` ');
                    }
                }else{
                    $tag_array = explode(',',$tag);
                    $subquery = 'tag = "' . $tag_array[0] . '"';
                    if (count($tag_array) > 1){
                        $i = 1;
                        while ($i < count($tag_array)){
                            $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                            $i++;
                        }
                    }
                    if ($this->own){
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=\''.$user_id.'\' AND deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') AND MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY `deck_id` ');
                    }else{
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) AND deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') GROUP BY `deck_id` ');
                    }
                }
            }else{
                if ($tag == 'all'){
                    if ($this->own){
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=\''.$user_id.'\' GROUP BY `deck_id` ');
                    }else{
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id GROUP BY `deck_id` ');
                    }
                }else{
                    $tag_array = explode(',',$tag);
                    $subquery = 'tag = "' . $tag_array[0] . '"';
                    if (count($tag_array) > 1){
                        $i = 1;
                        while ($i < count($tag_array)){
                            $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                            $i++;
                        }
                    }
                    if ($this->own){
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=\''.$user_id.'\' AND deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') GROUP BY `deck_id` ');
                    }else{
                        $languages = $this->dbGetCol('SELECT deck.language FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') GROUP BY `deck_id` ');
                    }
                }
            }
            
            if ($languages){
                $result_language = array();
                $languages_arr = array_unique(array_filter($languages));
                $key = array_search('-', $languages_arr); //delete empty languages
                if ($key !== false){
                    unset($languages_arr[$key]);
                }
                foreach ($languages_arr as $language){ //take the language name 
                    $result_language['db'] = $language;
                    $tmp_array = array();
                    $tmp_array = explode('-',$language);
                    $result_language['name'] = $tmp_array['1'];
                    $this->languages[] = $result_language;
                }
                if (!$current){
                    $current = 'all languages';
                }else{
                    $all_array = array();
                    $all_array['db'] = 'all languages';
                    $all_array['name'] = 'all languages';
                    $this->languages[] = $all_array;
                    $current_arr = explode('-', $current);
                    $current = $current_arr['1'];
                    foreach($this->languages as $key => $language){
                        if ($language['name'] == $current){
                            unset($this->languages[$key]);
                        }
                    }
                }
                $this->current = $current;
            }
        }
        
	public function searchMatch($keywords, $tag, $order, $page, $user_id = false, $language = false) {
		$query='';
		$cond1="";
		$cond11="";
		if($order=='featured'){
                    $cond1=" AND is_featured=1 ";
                    $cond11=" WHERE is_featured=1 ";	
		}
		switch ($order) {			
			case 'date' : $order_db='deck_revision.timestamp DESC '; break;
			case 'title' : $order_db='`title` ASC '; break;
			case 'popularity' : $order_db='`popularity` DESC '; break;
			default: $order_db='deck_revision.timestamp DESC ';		
		}
                
                if ($language == 'all languages') $language = false;
                $this->createLanguageList($keywords, $tag, $language, $user_id);
                
                if ($this->own){//only own decks
                    if (!$language){
                        if (strlen($keywords)>2) {
                            if ($tag=='all') {				
                                    $query = 'SELECT * FROM deck_revision WHERE deck_revision.user_id=:user_id AND MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)'.$cond1.' GROUP BY `deck_id` ORDER BY '  . $order_db  ;
                            } else {
                                
                                $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                $query =  'SELECT * FROM deck_revision WHERE deck_revision.user_id=:user_id AND id IN
                                            (SELECT item_id FROM tag WHERE ' . $subquery . ') AND  MATCH (title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) 
                                    '.$cond1.'GROUP BY `deck_id` ORDER BY ' . $order_db  ;		
                            }
                        } else {
                                if ($tag=='all') {				
                                        $query =  'SELECT * FROM deck_revision ' .$cond11.' WHERE deck_revision.user_id=:user_id GROUP BY `deck_id` ORDER BY  ' . $order_db   ;
                                } else {
                                    $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                $query =  'SELECT * FROM deck_revision WHERE deck_revision.user_id=:user_id AND id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') '.$cond11.'GROUP BY `deck_id` ORDER BY ' . $order_db ;	
                                }
                        }
                    
                        $result = $this->dbQuery ($query,array('user_id' => $user_id));
                    }else{
                        if (strlen($keywords)>2) {
                            if ($tag=='all') {				
                                    $query = 'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=:user_id AND deck.language=:language AND MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)'.$cond1.' GROUP BY `deck_id` ORDER BY '  . $order_db  ;
                            } else {
                                $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                    $query =  'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=:user_id AND deck.language=:language AND deck_revision.id IN
                                            (SELECT item_id FROM tag WHERE ' . $subquery . ') AND  MATCH (title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) 
                                    '.$cond1.'GROUP BY `deck_id` ORDER BY ' . $order_db  ;		
                            }
                        } else {
                                if ($tag=='all') {				
                                        $query =  'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id' .$cond11.' WHERE deck_revision.user_id=:user_id AND deck.language=:language GROUP BY `deck_id` ORDER BY  ' . $order_db   ;
                                } else {
                                    $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                        $query =  'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.user_id=:user_id AND deck.language=:language AND deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') '.$cond11.'GROUP BY `deck_id` ORDER BY ' . $order_db ;	
                                }
                        }
                        $result = $this->dbQuery ($query,array('language'=>$language,'user_id' => $user_id));
                    }
                    $total = count($result);
                    
                    //paged
                    if ($page>1)
                        $query = $query. 'LIMIT '. strval(($page-1)*$this::decks_per_page) .','. strval($this::decks_per_page) ;
                    else 
                        $query = $query. 'LIMIT 0,'. strval($this::decks_per_page) ;
                    if (!$language){
                        $result = $this->dbQuery ($query,array('user_id' => $user_id));
                    }else{
                        $result = $this->dbQuery ($query,array('language'=>$language,'user_id' => $user_id));
                    }
                }else{ //search decks of all users
                    if (!$language){
                        if (strlen($keywords)>2) {
                                if ($tag=='all') {				
                                        $query = 'SELECT * FROM deck_revision WHERE MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)'.$cond1.' GROUP BY `deck_id` ORDER BY '  . $order_db  ;
                                } else {
                                    $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                        $query =  'SELECT * FROM deck_revision WHERE id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') AND  MATCH (title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) 
                                        '.$cond1.'GROUP BY `deck_id` ORDER BY ' . $order_db  ;		
                                }
                        } else {
                                if ($tag=='all') {				
                                        $query =  'SELECT * FROM deck_revision' .$cond11.' GROUP BY `deck_id` ORDER BY  ' . $order_db   ;
                                } else {
                                    $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                        $query =  'SELECT * FROM deck_revision WHERE id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') '.$cond11.'GROUP BY `deck_id` ORDER BY ' . $order_db ;	
                                }
                        }
                        $result = $this->dbQuery ($query);
                    }else{
                        if (strlen($keywords)>2) {
                                if ($tag=='all') {				
                                        $query = 'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE MATCH(title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)'.$cond1.' AND deck.language=:language GROUP BY `deck_id` ORDER BY '  . $order_db  ;
                                } else {
                                    $tag_array = explode(',',$tag);
                                $subquery = 'tag = "' . $tag_array[0] . '"';
                                if (count($tag_array) > 1){
                                    $i = 1;
                                    while ($i < count($tag_array)){
                                        $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                        $i++;
                                    }
                                }
                                        $query =  'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ')  AND deck.language=:language AND  MATCH (title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) 
                                        '.$cond1.'GROUP BY `deck_id` ORDER BY ' . $order_db  ;		
                                }
                        } else {
                                if ($tag=='all') {				
                                        $query =  'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck.language=:language' .$cond11.' GROUP BY `deck_id` ORDER BY  ' . $order_db   ;
                                } else {
                                    $tag_array = explode(',',$tag);
                                    $subquery = 'tag = "' . $tag_array[0] . '"';
                                    if (count($tag_array) > 1){
                                        $i = 1;
                                        while ($i < count($tag_array)){
                                            $subquery .= 'OR tag = "' . $tag_array[$i] . '"';
                                            $i++;
                                        }
                                    }
                                        $query =  'SELECT * FROM deck_revision JOIN deck ON deck_revision.deck_id=deck.id WHERE deck_revision.id IN
                                                (SELECT item_id FROM tag WHERE ' . $subquery . ') '.$cond11.' AND deck.language=:language GROUP BY `deck_id` ORDER BY ' . $order_db ;	
                                }
                        }
                        $result = $this->dbQuery ($query,array('language'=>$language));
                    }
                    $total = count($result);
                    
                    //paged
                    if ($page>1)
                        $query = $query. 'LIMIT '. strval(($page-1)*$this::decks_per_page) .','. strval($this::decks_per_page) ;
                    else 
                        $query = $query. 'LIMIT 0,'. strval($this::decks_per_page) ;
                    if (!$language){
                        $result = $this->dbQuery ($query);
                    }else{
                        $result = $this->dbQuery ($query,array('language'=>$language));
                    }
                }
                $this->pager($total, $page);
		return $result;
	}
        
        public function removeLoopingDecks ($selectedId){
		$i=0;		
		$selected_id_array = explode( '-',$selectedId);
		if ($selected_id_array[2]=="slide") 
			$selectedDeckId = $selected_id_array[1]; 
		if  ($selected_id_array[2]=="deck")
			$selectedDeckId= $selected_id_array[3];		
		for($i=0;$i<count ($this->decks);$i++) {			
			$newDeck = new Deck();			
			$newDeck->id = $this->decks[$i]->id;			
				//check for endless loops
			if ($newDeck->parentsChildren($selectedDeckId)!=0) {
				$this->decks[$i]->loop='yes';
			}else {
                            $this->decks[$i]->loop='no';
                        }		
		}		
	}
	public function alignAllWrongPositions (){
			foreach ( $this->dbQuery ( 'SELECT deck_revision_id,position,count(*) as total FROM deck_content GROUP BY deck_revision_id ORDER BY position ASC' ) as $row ) {
			    $i=1;
				foreach ( $this->dbQuery ( 'SELECT position FROM deck_content WHERE deck_revision_id='.$row ['deck_revision_id'].' ORDER BY position ASC' ) as $row2 ) {
					echo $row ['deck_revision_id'].' : '.$row2 ['position'] ;
					if($row2 ['position']!=$i){
						echo "Inconsistency found!";
						$this->dbQuery ( 'UPDATE  deck_content SET position='.$i.' WHERE deck_revision_id='.$row ['deck_revision_id'].' AND position='.$row2 ['position']);
						echo "<br>Fixed!<br>" ;
					}
					echo "<br>" ;
					$i++;
				}
		}
	}		
		
	
//	public function Build( ) {	
//		
//		$k = 0; //counter for numeration
//		
//			
//		echo '';
//		
//		foreach($this->decks as $last)
//		{
//	
//			
//                        echo '</h3></div>';
//                        if (isset($last['loop']) && $last['loop'] == 'no'){
//			echo '';
//                        }else {
//                            if(isset($last['loop'])){
//                                echo '<div>Can\'t append this deck</div>';   
//                            }   
//                        }
//                        echo '<div>&nbsp;</div>';
//                        echo '</div>';
//			echo '<div style="display:none; padding:0" id="slide_viewer_'.$last->id.'" class="deck-menu deck-container" style="width:100% !important; padding: 0 !important">';
//			$slide_array = $last->getSlides();
//			foreach( $slide_array as $slide ){
//				$i++;
//				if ($i <= 4) {                                    
//					echo '<div onclick="goToSlide(this.id)" class="slide" id="./?url=main/deck&deck='.$last->id.'#tree-'.$slide->deck.'-slide-'.$slide->id.'-'.$slide->position.'-view">';
//					echo $slide->getThumbnailContent();
//					echo '</div>';
//				}
//			}
//                        
//                        echo '</div>';			
//			
//		}
//			
//		echo '</ul>';
//		
//	} 
	
	
}
