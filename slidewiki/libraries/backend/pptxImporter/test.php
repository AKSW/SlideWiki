<?php
	require_once 'pptxPresentation.php';
	
	
	$pr = new pptxPresentation('../../Templates.pptx');
	//$pr = new pptxPresentation('../../creatingknowledge.pptx');
	
	$_SESSION['uid'] = 2;
	//$pr = new pptxPresentation('testSamples/technology.pptx');
	
	var_dump(gd_info());
	//var_dump( $pr->_slides[1]->_html_string);
	
	//echo $pr->_slides[19]->toString();
	//$pr->saveToDB();
	//echo $pr->_slides[13]->toString();
	
	
	//$pr = new pptxPresentation('../../creatingknowledge.pptx');
	//echo $pr->_slides[0]->_shapes[2]->toString();
	//var_dump($txBody->p[$par_num]->r);

?>