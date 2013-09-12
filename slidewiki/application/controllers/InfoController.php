<?php
class InfoController extends Controller {

	function termsOfUse() {
		$this->set ( 'page_title', 'Terms of Use - SlideWiki' );
	}
	function imprint() {
		$this->set ( 'page_title', 'Imprint - SlideWiki' );
	}	

}