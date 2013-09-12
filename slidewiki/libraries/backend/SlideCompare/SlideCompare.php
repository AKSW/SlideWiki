<?php
class SlideCompare {
	
	function tidyhtml($input) {
		$config = array(
			   'show-errors'         => 0,
			   'show-warnings'         => false,
			   'break-before-br'         => true,
			   'indent'         => false,
			   'indent-attributes'         => true,
			   'add-xml-decl'   => false,
			   'force-output'   => true,
			   'fix-backslash'   => false,
			   'merge-divs'   => false,
			   'merge-spans'   => false,
			   'doctype'   => 'omit',
			   'enclose-block-text'   => false,
			   'drop-empty-paras'   => false,
			   'output-html'   => true,
			   'show-body-only'   => true,
			   'wrap'           => 1);

		$tidy = new tidy;
		$tidy->parseString($input, $config, 'utf8');
		$tidy->cleanRepair();

		// Output
		return $tidy;
	}
	
	Static public function compareSlideToSlide($old_slide, $new_slide) {
		$compare_result = NULL;
		
		$str1 = $old_slide->content;
		$str2 = $new_slide->content;
		
		// normalize the HTML content
		$str1 = SlideCompare::tidyhtml($str1);
		$str2 = SlideCompare::tidyhtml($str2);
		
		$string_compare_result = SlideCompare::compareStringToString($str1, $str2);
		
		if($string_compare_result === "") {
			$slides_identical = true;
		} else {
			$slides_identical = false;
		}
		
		return $slides_identical;
	}
	
	Static public function compareSlideToString($slide_content, $str) {
		$compare_result = NULL;	
		
		$str1 = SlideCompare::tidyhtml($slide_content);
		$str2 = $str;
		
		$compare_result = SlideCompare::compareStringToString($str1, $str2);
		
		return $compare_result;
	}
	
	Static public function compareStringToString($str1, $str2) {
		$compare_result = NULL;
		
		// wrapping the words
		$str1 = wordwrap($str1, 1);
		$str2 = wordwrap($str2, 1);
		// Diff
		$compare_result = xdiff_string_diff($str1, $str2);
				
		return $compare_result;
	}
}
?>