<?php

include_once(ROOT . DS . "libraries" . DS . "backend" . DS . "CreateZipFile" . DS . "CreateDirZip.php");
include_once(ROOT . DS . "libraries" . DS . "backend" . DS . "pclzip" . DS . "pclzip.lib.php");

class ExportController extends Controller {	
	function createSlideString($deck_id){
		$slides_to_string ='';
		$deck = new Deck;
		$deck->createFromID($deck_id);	
		$deck->content = $deck->fetchDeckContent ();
		foreach ($deck->content  as $element){
			if (get_class ( $element ) == "Slide") {
				$slides_to_string = $slides_to_string.'<div class="slide" id="tree-'.$element->deck.'-slide-'.$element->id.'-'.$element->position.'-view">'.$element->content.'</div>'.PHP_EOL;
                        }else{
				$slides_to_string = $slides_to_string.'<div class="deck" id="tree-'.$deck->id.'-deck-'.$element->id.'-'.$element->position.'">'. $this->createSlideString($element->id).'</div>' ;
			}
		}
                $img_pattern = '/\.\/upload\/media\/images\/\d+\//';
                $slides_to_string = preg_replace($img_pattern, '', $slides_to_string);
		return $slides_to_string;
	}       
        function toSCO(){
            $deck_id = $_GET['deck_id'];
            if (isset($_GET['format'])){
                $format = $_GET['format']; 
            }else{
                $format = 'scorm2004_3rd';
            }
            $scorm = new Scorm();
            $scorm->create($deck_id, $format);
            
            $deck_name = $scorm->root_deck_name;

            $archive = new PclZip($deck_name .'.zip');

            //adding sco universal metadata
            $v_list = $archive->create (
                    ROOT . DS . 'libraries' . DS . 'backend' . DS . $format,
                    PCLZIP_OPT_REMOVE_PATH, ROOT . DS . 'libraries' . DS . 'backend' . DS . $format,
                    PCLZIP_OPT_ADD_TEMP_FILE_ON);
            if ($v_list == 0){
                die("Error : ".$archive->errorInfo(true));
            }

            //adding sco from tmp
            $v_list = $archive->add (
                    ROOT . DS . 'tmp' . DS . $deck_name ,
                    PCLZIP_OPT_REMOVE_PATH, ROOT . DS . 'tmp' . DS . $deck_name,
                    PCLZIP_OPT_ADD_TEMP_FILE_ON);
            if ($v_list == 0){
                die("Error : ".$archive->errorInfo(true));
            }
            $archive->force_download();

            chmod(ROOT . DS . $deck_name .'.zip', 0777);
            unlink(ROOT . DS . $deck_name .'.zip');
            $this->RemoveDir(ROOT . DS . 'tmp' . DS . $deck_name);
        }

        function RemoveDir($path){
            if(file_exists($path) && is_dir($path)){
                $dirHandle = opendir($path);
                while (false !== ($file = readdir($dirHandle))){
                    if ($file!='.' && $file!='..'){
                        // delete the folders '.' and '..' 
                        $tmpPath=$path.'/'.$file;
                        chmod($tmpPath, 0777);

                        if (is_dir($tmpPath)){  // if the folder
                            $this->RemoveDir($tmpPath);
                        } 
                        else{ 
                            if(file_exists($tmpPath)){
                                // delete file
                                unlink($tmpPath);
                            }
                        }
                    }
                }
                closedir($dirHandle);

                // delete current folder
                if(file_exists($path)){
                    rmdir($path);
                }
            }
            else{
                echo "The folder does not exist!";
            }
        }
	function toHTML() {
		$deck_id = $_GET['deck_id'];
		
		$deck = new Deck;
		$deck->createFromID($deck_id);
		$slides = $deck->getSlidesFull();
		$slides_to_string = array();
		$image_local_path = array();
		$image_name = array();
		$image_paths = array();
		$slides_to_string = $this->createSlideString($deck_id);
		foreach ($slides  as $slide)
                {
        	$img_pattern = '/\.\/upload\/media\/images\/\d+\/\d+\..../';
			preg_match_all($img_pattern, $slide->content, $matches); 
			if(count($matches[0])){
				foreach($matches[0] as $img_path) {
					$image_paths[]=$img_path;
				}
			}
                        
                }
                $html = " 
		<!DOCTYPE html>
		<!--[if lt IE 7]> <html class=\"no-js ie6\" lang=\"en\"> <![endif]-->
		<!--[if IE 7]>    <html class=\"no-js ie7\" lang=\"en\"> <![endif]-->
		<!--[if IE 8]>    <html class=\"no-js ie8\" lang=\"en\"> <![endif]-->
		<!--[if gt IE 8]><!-->  <html class=\"no-js\" lang=\"en\"> <!--<![endif]-->
		<head>
			<meta charset=\"utf-8\">
			<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">

			<title>". $deck->title ."</title>

			<meta name=\"author\" content=". $deck->owner->username .">
			<meta name=\"deck_id\" content=". $deck->deck_id .">
			<meta name=\"deck_revision_id\" content=". $deck->id .">
			<meta name=\"viewport\" content=\"width=1024, user-scalable=no\">

			<!-- Core and extension CSS files -->
			<link rel=\"stylesheet\" href=\"deck.js/core/deck.core.css\">
			<link rel=\"stylesheet\" href=\"deck.js/extensions/goto/deck.goto.css\">
			<link rel=\"stylesheet\" href=\"deck.js/extensions/menu/deck.menu.css\">
			<link rel=\"stylesheet\" href=\"deck.js/extensions/navigation/deck.navigation.css\">
			<link rel=\"stylesheet\" href=\"deck.js/extensions/status/deck.status.css\">
			<link rel=\"stylesheet\" href=\"deck.js/extensions/hash/deck.hash.css\">

			<!-- Theme CSS files (menu swaps these out) -->
			<link rel=\"stylesheet\" id=\"style-theme-link\" href=\"default_theme/default.css\">
			<link rel=\"stylesheet\" id=\"transition-theme-link\" href=\"deck.js/themes/transition/horizontal-slide.css\">

			<!-- Custom CSS just for this page -->
			<link rel=\"stylesheet\" href=\"deck.js/introduction/introduction.css\">
			<link rel=\"stylesheet\" href=\"local.css\">

			<script src=\"deck.js/modernizr.custom.js\"></script>
			<script src=\"deck.js/jquery.js\"></script>

			<!-- client syntax highlightning -->
			<link   href=\"deck.js/syntax/shThemeDefault.css\" rel=\"stylesheet\" type=\"text/css\" />
			<script src=\"deck.js/syntax/shCore.js\" type=\"text/javascript\"></script>
			<script src=\"deck.js/syntax/shAutoloader.js\" type=\"text/javascript\"></script>
			<script src=\"deck.js/syntax/shBrushTurtle.js\" type=\"text/javascript\"></script>
			<script type=\"text/javascript\" src=\"deck.js/MathJax.js\"></script>

<script type=\"text/javascript\">
			function htmlDecode(input){
				  var e = document.createElement(\"div\");
				  e.innerHTML = input;
				  return e.childNodes.length === 0 ? \"\" : e.childNodes[0].nodeValue;
			}
			function removeBRs(input){
				var r=input.replace(/<br>/gi,\"\"); 
				r=r.replace(/<br[0-9a-zA-Z]+>/gi,\"\");
				return r;
			}
				SyntaxHighlighter.defaults['toolbar'] = false;
				SyntaxHighlighter.all();
				</script>
			<script type=\"text/x-mathjax-config\">
			$(function() {
			  MathJax.Hub.Config({
					skipStartupTypeset: true,
					extensions: [\"tex2jax.js\"],
					jax: [\"input/TeX\", \"output/HTML-CSS\"],
					tex2jax: {
					inlineMath: [ ['$','$'], [\"\\(\",\"\\)\"] ],
					displayMath: [ ['$$','$$'], [\"\\[\",\"\\]\"] ],
					processEscapes: true
					},
					\"HTML-CSS\": { availableFonts: [\"TeX\"] }
			  });
				$.deck('.slide');
				MathJax.Hub.Queue([\"Typeset\",MathJax.Hub,'slide-area']);
				});
				</script>			
		</head>		
		
		<body class=\"deck-container\">

			<div class=\"theme-menu\">
				<h2>Themes</h2>

				<label for=\"style-themes\">Style:</label>
				<select id=\"style-themes\">
					<option selected value=\"default_theme/default.css\">Default</option>
					<option value=\"deck.js/themes/style/web-2.0.css\">Web 2.0</option>
					<option value=\"deck.js/themes/style/swiss.css\">Swiss</option>
					<option value=\"deck.js/themes/style/neon.css\">Neon</option>
					<option value=\"\">None</option>
				</select>

				<label for=\"transition-themes\">Transition:</label>
				<select id=\"transition-themes\">
					<option selected value=\"deck.js/themes/transition/horizontal-slide.css\">Horizontal Slide</option>
					<option value=\"deck.js/themes/transition/vertical-slide.css\">Vertical Slide</option>
					<option value=\"deck.js/themes/transition/fade.css\">Fade</option>
					<option value=\"\">None</option>
				</select>
			</div>" . 
				$slides_to_string . 
				"</div>
			</div>
		</div>
		
		<a href=\"#\" class=\"deck-prev-link\" title=\"Previous\">&#8592;</a>
		<a href=\"#\" class=\"deck-next-link\" title=\"Next\">&#8594;</a>

		<p class=\"deck-status\">
			<span class=\"deck-status-current\"></span>
			/
			<span class=\"deck-status-total\"></span>
		</p>

		<form action=\".\" method=\"get\" class=\"goto-form\">
			<label for=\"goto-slide\">Go to slide:</label>
			<input type=\"number\" name=\"slidenum\" id=\"goto-slide\">
			<input type=\"submit\" value=\"Go\">
		</form>

		<a href=\".\" title=\"Permalink to this slide\" class=\"deck-permalink\">#</a>


		<!-- Deck Core and extensions -->
		<script src=\"deck.js/core/deck.core.js\"></script>
		<script src=\"deck.js/extensions/menu/deck.menu.js\"></script>
		<script src=\"deck.js/extensions/goto/deck.goto.js\"></script>
		<script src=\"deck.js/extensions/status/deck.status.js\"></script>
		<script src=\"deck.js/extensions/navigation/deck.navigation.js\"></script>
		<script src=\"deck.js/extensions/hash/deck.hash.js\"></script>
		<!-- Specific to this page -->
		<script src=\"deck.js/introduction/introduction.js\"></script>
		<!-- Code Mirror -->
		<link rel=\"stylesheet\" href=\"codemirror/lib/codemirror.css\">
		<script src=\"codemirror/lib/codemirror.js\"></script>
		<script src=\"codemirror/mode/javascript/javascript.js\"></script>
		<script src=\"codemirror/mode/xml/xml.js\"></script>
		<script src=\"codemirror/mode/htmlmixed/htmlmixed.js\"></script>
		<script src=\"codemirror/mode/css/css.js\"></script>
		<script src=\"codemirror/mode/php/php.js\"></script>
		<script src=\"codemirror/mode/ntriples/ntriples.js\"></script>
		<script src=\"codemirror/mode/sparql/sparql.js\"></script>
		<script src=\"codemirror/lib/util/formatting.js\"></script>
		<!-- Code Mirror for deck.js -->
		<link rel=\"stylesheet\" href=\"deck.js/extensions/codemirror/deck.codemirror.css\">
		<script src=\"deck.js/extensions/codemirror/deck.codemirror.js\"></script>
		</body>
		</html>";
		$zipName="archive.zip";
		
		$libraries_folder = 'deck.js' . DS;
		
		$createZipFile = new createDirZip;
		$createZipFile->get_files_from_folder(ROOT . DS . 'libraries' . DS . 'frontend' . DS . 'deck.js' . DS, $libraries_folder);
		$fileToZip = "index.html";
		$createZipFile->addFile($html, $fileToZip);
		$fileContents = file_get_contents(ROOT . DS . "libraries" . DS . "frontend" . DS . "MathJax" . DS ."MathJax.js");
		$fileToZip = $libraries_folder."MathJax.js";
		$createZipFile->addFile($fileContents, $fileToZip);
		$fileContents = file_get_contents(ROOT . DS . "libraries" . DS . "frontend" . DS ."jquery.js");
		$fileToZip = $libraries_folder."jquery.js";
		$createZipFile->addFile($fileContents, $fileToZip);
		$fileContents = file_get_contents(ROOT . DS . "libraries" . DS . "frontend" . DS ."jquery.min.js");
		$fileToZip = $libraries_folder."jquery.min.js";
		$createZipFile->addFile($fileContents, $fileToZip);
		$createZipFile->get_files_from_folder(ROOT . DS . 'libraries' . DS . 'frontend' . DS . 'codemirror' . DS, 'codemirror'. DS);	
		// adding images
		foreach($image_paths as $im){
			$fileContents = file_get_contents($im);
			$fileToZip = split('\/',$im);
			$fileToZip = $fileToZip[count($fileToZip)-1];
                        $createZipFile->addFile($fileContents, $fileToZip);
		}
		//add default theme
		$style = new Style();
		$tmp=$style->getStyle($deck->default_theme);
		$createZipFile->addFile($tmp['css'], 'default_theme/default.css');
		$date = date('Y-m-d');
		$zipName = $this->sluggify($deck->title).'_'.$date."_".$zipName;
		$fd = fopen($zipName, "wb");
		$out = fwrite($fd,$createZipFile->getZippedfile());
		fclose($fd);
		$createZipFile->forceDownload($zipName);
		@unlink($zipName);
	}
	function locateImages($html) {
		
	}
	function sluggify($url){
	    # Prep string with some basic normalization
	    $url = strtolower($url);
	    $url = strip_tags($url);
	    $url = stripslashes($url);
	    $url = html_entity_decode($url);
	
	    # Remove quotes (can't, etc.)
	    $url = str_replace('\'', '', $url);
	
	    # Replace non-alpha numeric with hyphens
	    $match = '/[^a-z0-9]+/';
	    $replace = '-';
	    $url = preg_replace($match, $replace, $url);
	
	    $url = trim($url, '-');
	
	    return $url;
    }
}