<?php

class IndexController extends Controller {
	function view() {
		if(isset($_GET['_escaped_fragment_']))
			$static=1;
		else 	
			$static=0;
		$this->set ( 'static',$static);
		$this->set ( 'page_additional_headers','<meta name="author" content="Ali Khalili, Darya Tarasowa, Soeren Auer">');
		$this->set ( 'page_additional_footer','<a href="?_escaped_fragment_">(Plain SlideWiki home page)</a>');
		$this->set ( 'page_title', 'SlideWiki: Authoring platform for OpenCourseWare' );
		$this->set ( 'page_keywords', 'SlideWiki, presentation, slides, CrowdLearn, AKSW, education, e-learning' );
		$this->set ( 'page_description', 'SlideWiki is a platform created as a proof of concept for CrowdLearn concept. SlideWiki empowers extremly large communities of instructors, teachers, lecturers, academics to create, share and re-use sophisticated educational content in a truly collaborative way.' );	
	}
}