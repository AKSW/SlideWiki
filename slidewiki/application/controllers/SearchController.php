<?php
class SearchController extends Controller {
	function view() {		
		
		if (isset($_GET['order'])){
			$order = $_GET['order'];
		}else{
			$order='date';
		}
		$this->set('order', $order);
		
		if (isset($_GET['page'])){
			$page = $_GET['page'];
		}else{
			$page=1;
		}
		$this->set('page', $page);
		
		if (isset($_GET['tag'])){
			$tag = $_GET['tag'];
		}else{
			$tag='all';
		}
		$this->set('tag', $tag);
	
		if (isset($_GET['keywords'])){
			$keywords = $_GET['keywords'];
		}else{
			$keywords='';
		}
		$this->set('keywords', $keywords);	
	
		if (isset($_GET['typeOfSearch'])) {
			$typeOfSearch = $_GET['typeOfSearch']; 
		}else {
			$typeOfSearch = 'both';
		}
		$this->set('typeOfSearch', $typeOfSearch);	
		if(trim($keywords)!='')
			$this->set ( 'page_title', 'SlideWiki -- Search for "'.$keywords.'"' );
		elseif($order=='date') 	
			$this->set ( 'page_title', 'SlideWiki -- Presentations ordered by date' );
		elseif ($order=='featured')
			$this->set ( 'page_title', 'SlideWiki -- Featured Presentations' );
	}
	function view_overlay() {
		
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		if (isset($_GET['order'])){
			$order = $_GET['order'];
		}else{
			$order='date';
		}
		$this->set('order', $order);
		
		if (isset($_GET['page'])){
			$page = $_GET['page'];
		}else{
			$page=1;
		}
		$this->set('page', $page);
	
		if (isset($_GET['keywords'])){
			$keywords = $_GET['keywords'];
		}else{
			$keywords='';
		}
		$this->set('keywords', $keywords);	
	
		if (isset($_GET['typeOfSearch'])) {
			$typeOfSearch = $_GET['typeOfSearch']; 
		}else {
			$typeOfSearch = 'both';
		}
		$this->set('typeOfSearch', $typeOfSearch);
		if (isset($_GET['selected_id'])) {
			$selected_id = $_GET['selected_id']; 
		}else {
			$selected_id = '0';
		}
		$this->set('selected_id', $selected_id);
	}
}