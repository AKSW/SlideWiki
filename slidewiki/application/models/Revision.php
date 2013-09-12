<?php
//Revision model handles new revisions created for decks	
class Revision extends Model {
	//the root of the selected tree
	public $root_id;
	//the array which acts as an input for jstree
	public $tree;
	//an array which stores the reference to each node's parent
	public $parentHash;
	function __construct($tree) {
		if(!$tree)
			return 0;
		$this->tree = $tree;
		$this->root_id = $this->getNodeRevID ( $this->getNodeID ( $tree [0] ) );
		$this->createParentHash ( $tree );
	}
	function createParentHash($node) {
		if (count ( $tmp = @$node [0] ['children'] ? $node [0] ['children'] : $node ['children'] ))
			foreach ( $tmp as $element ) {
				//var_dump($element);
				if ($this->getNodeType ( $element ) == 'deck')
					$this->createParentHash ( $element );
				$this->parentHash [$this->getNodeID ( $element )] = $this->getNodeID ( @$node [0] ? $node [0] : $node );
			}
	}
	function getNodeType($element) {
		$id = $this->getNodeID ( $element );
		$type = $this->getNodeTypeFromID ( $id );
		return $type;
	}
	function getNodeID($element) {
		return $element ['data'] ['attr'] ['id'];
	}
	function getNodeRevID($node_id) {
		$tmp = explode ( '-', $node_id );
		return $tmp [3];
	}
	function getNodePositionFromID($node_id) {
		$tmp = explode ( '-', $node_id );
		return $tmp [4];
	}
	function getNodeTypeFromID($node_id) {
		$tmp = explode ( '-', $node_id );
		return $tmp [2];
	}
	function getNodeParentRevID($node_id) {
		$tmp = explode ( '-', $node_id );
		return $tmp [1];
	}
	//todo: optimize this function
	function getDepthOfTree() {
		$arr = $this->tree [0] ['children'];
		if (! is_array ( $arr )) {
			return 0;
		}
		$arr = json_encode ( $arr );
		
		$varsum = 0;
		$depth = 0;
		for($i = 0; $i < strlen ( $arr ); $i ++) {
			$varsum += intval ( $arr [$i] == '[' ) - intval ( $arr [$i] == ']' );
			if ($varsum > $depth) {
				$depth = $varsum;
			}
		}
		
		return $depth - 2;
	}
	//tip: here the change type is not important only the deck/decks which is/are the target of change is/are important
	//this function only handles deck revisions the response would be as an input for a function which handles changes in leaves
	//$changes=array('user_id'=>'user_id','items'=>array(array('item_id'=>'itemId1'),array('item_id'=>'itemId2'),.....)));
	//output=array('root_changed'=>0/new_root_id, 'items'=>array(array('target_deck_id'=>change1),array('target_deck_id'=>change2)));
	function handleChange($changes) {
		//we store new deck revs to prevent duplicate rev creation in case we have changed two items of a deck
		$new_deck_revs = array ();
		//result of change applied to each change item
		$items=array();
		$should_refresh_nodes=array();
		//create user who has made the change
		$user = new User ();
		$user->createFromID ( $changes ['user_id'] );
		foreach ( $changes ['items'] as $change ) {
			//get the path to the root
			$nested_decks = $this->getNestedDecks ( $change ['item_id'] );
			//get the target deck of change
			if (count ( $nested_decks ) == 1) {
				//root node is selected
				$target_deck_id = $this->root_id;
			} else {
				$target_deck_id = $nested_decks [0];
			}
			//create target deck object
			$target_deck = new Deck ();
			$target_deck->createFromIDLite ( $target_deck_id );
			$target_deck->content = $target_deck->fetchDeckContentLite ();
			//check if we need to create new revisions
			if (! @$new_deck_revs [$target_deck->id] && $this->needNewRevision ( $user->id, $target_deck )) {
				$target_deck->user = $user;
				$new_deck_revs [$target_deck_id] = $target_deck->commit ( $target_deck_id );
				$i = 1;
				while ( @$nested_decks [$i] && ( int ) $nested_decks [$i] != 0 ) {
					$tmp_deck = new Deck ();
					if(@$new_deck_revs [$nested_decks [$i]]){
						$tmp_deck->createFromIDLite ( $new_deck_revs [$nested_decks [$i]] );
						$tmp_deck->content = $tmp_deck->fetchDeckContentLite ();
					}else{
						$tmp_deck->createFromIDLite ( $nested_decks [$i] );
						$tmp_deck->content = $tmp_deck->fetchDeckContentLite ();
					}
					if (! @$new_deck_revs [$tmp_deck->id] && $this->needNewRevision ( $user->id, $tmp_deck )) {
						$tmp_deck->user = $user;
						$new_deck_revs [$nested_decks [$i]] = $tmp_deck->commit ( $tmp_deck->id );
					}else{
						$should_refresh_nodes[]=$tmp_deck->id ;
					}
					if (@$new_deck_revs [$nested_decks [$i - 1]]) {
						//we should update deck content
						$tmp_deck->replaceContentWith ( $new_deck_revs [$nested_decks [$i - 1]], $nested_decks [$i - 1], 'deck' );
					} else {
						//we do not need to iterate more
						break;
					}
					$i ++;
				}
			}else{
				//stop refreshing here
				$should_refresh_nodes[]=$target_deck_id;
			}
			//check if target node is changed to a new revision
			if (count ( $nested_decks ) == 1) {
				//root node is selected
				$target_deck_id = @$new_deck_revs [$this->root_id] ? $new_deck_revs [$this->root_id] : $this->root_id;
			} else {
				$target_deck_id = @$new_deck_revs [$nested_decks [0]] ? $new_deck_revs [$nested_decks [0]] : $nested_decks [0];
			}
			//contains the id of leave deck whether it has a new revision or not
			$items[]=array('target_deck_id'=>$target_deck_id);
		}
		$output = array ();
		$output['items']=$items;
		$output['refresh_nodes']=$should_refresh_nodes;
		$output['new_deck_revs']=$new_deck_revs;
		if (@$new_deck_revs [$this->root_id]) {
			//root node has changed!
			$this->root_id = $new_deck_revs [$this->root_id];
			$output ['root_changed'] = $this->root_id;
                        $deck_for_title = new Deck();
                        $deck_for_title->id = $this->root_id;
                        $deck_for_title->title = $deck_for_title->getTitle();
                        $deck_for_title->slug_title = $deck_for_title->sluggify($deck_for_title->title);
                        $output['slug_title'] = $deck_for_title->slug_title;
		} else {
			//no change in root node
			$output ['root_changed'] = 0;
		}
		return $output;
	}
	//output: the recursive path to the root from the point the change occures
	function getNestedDecks($node_id) {
		$res = array ();
		while ( $parent_id = $this->getNodeParentRevID ( $node_id ) ) {
			$res [] = $parent_id;
			$node_id = $this->parentHash [$node_id];
		}
		//0 for end of path
		$res [] = 0;
		return $res;
	}
	//determines whether we need to create a new revision or not
	public function needNewRevision($user_id, $deck) {
		if (! $user_id)
			return - 1; // user is not logged in
		$user=new User();
		$user->createFromID($user_id);	
		if (($user_id != $deck->owner->id) && !$user->isMemberOfGroup($deck->id, 'editor')) {
			//user is not the owner of the deck
			return 1;
		} else {
			//user is the owner of the deck or in editorial list
			//check deck usage
			if (count ( $deck->getUsageExceptUser($user_id,1))) {
				//deck is used somewhere else
				return 1;
			} else {
				//deck is not used somewhere else
				return 0;
			}
		}
	}
}
	
