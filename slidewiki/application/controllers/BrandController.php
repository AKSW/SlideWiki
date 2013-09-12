<?php
class BrandController extends Controller {
	function create() {
		if($this->_user ['id']==2 || $this->_user ['id']==1){
			$this->set('alert', '1');
		}else{
			$this->set('alert', '0');
		}
		if (isset ( $_POST ['submit'] )) {
			$brand=$_POST ['brand'];
			$br=new Brand();
			$br->description=$brand['text'];
			$br->image=$brand['image'];
			$br->url=$brand['url'];
			$br->deck_id=$brand['deck'];
			$br->create();
                        $deck = new Deck();
                        $deck->id = $brand['deck'];
                        $deck->title = $deck->getTitle();
                        $deck->slug_title = $deck->sluggify($deck->title);
			header('Location: deck/'.$brand['deck'].'_'.$deck->slug_title);
		}else{
			
		}
	}
}
