<?php
	include_once ROOT . DS .'libraries'. DS .'backend'. DS .'simple_html_dom'. DS .'simple_html_dom.php';

	class texPresentation {
	
		public $_deck_num;
	
		public function __construct($tex_uri, $user_id, $type) {			
			
			// go to the user folder
			chdir("./upload/tex/$user_id/");
			
			$tex_filename = $this->getFilenameFromPath($tex_uri);
			$full_filename = implode($tex_filename, ".");
			$filename_ext = array_pop($tex_filename);
			$short_filename = array_pop($tex_filename);	// without extension	
			
			switch($type) {
				// single .tex file
				case 'application/octet-stream':
				case 'text/x-tex':
					mkdir("./$short_filename");
					rename("$full_filename", "$short_filename/$full_filename");
					chdir("$short_filename");
					$this->parseLatexFile($full_filename);
					break;
				
				// .zip archive
				case 'application/zip':
					$dir_name = $this->unpackZipArchive($short_filename, $filename_ext);
					chdir("$short_filename");
					
					$this->parseLatexFile("$short_filename" . "." . "tex");
					
					break;
			}
			
			
			
			//extracting hooks to the new config file
			
			
			// return current directory to root
			//chdir("../../../");			
			
			// run htlatex on the file
			
			//$slides = $html->find('div[class=slide]');
			
			//$this->_deck_num = $this->saveToDB($user_id, $slides);
			
		}
		
		private function unpackZipArchive($short_filename, $filename_ext) {
			$zip = new ZipArchive;
			$res = $zip->open("$short_filename" . "." . "$filename_ext");
			if ($res === TRUE) {
				$zip->extractTo("./");
				$zip->close();
				return true;
			} else {
				die("Could not extract .zip archive.");
			}
		}
		
		private function parseLatexFile($full_filename) {
			$tex = file($full_filename);
			
			// look for \documentclass string (array element)
			$search_strings = array();
			array_push($search_strings, "documentclass");
			array_push($search_strings, "documentstyle");
			array_push($search_strings, "tex4ht");
			
			$positions = $this->lookForAString($tex, $search_strings);
			
			$tex_insert_position = 0;
			
			if(array_key_exists("documentclass",$positions)) {
				$tex_insert_position = $positions["documentclass"] + 1;
			} elseif(array_key_exists("documentstyle",$positions)) {
				$tex_insert_position = $positions["documentstyle"] + 1;
			}
			
			if($tex_insert_position == 0) {
				die("Could not find \\documentclass or \\documentstyle declaration in your .tex file.");
			}
			
			if(array_key_exists("tex4ht", $positions)) {
				die("Your .tex file have tex4ht definition on line " . $positions["tex4ht"] . ", please delete it!");
			}
			
			// insert \nonstopmode at the beginning of the file
			array_splice($tex, 0, 0, "\\nonstopmode\r\n");
			
			// save everything to file
			$tex = implode($tex);
			$fp = fopen($full_filename, "w") or die("Couldn't open file"); 
			$numBytes = fwrite($fp, $tex); 
			fclose($fp); 
			
			// open slidewiki config file
			$slidewiki_config_path = "../../../../libraries/backend/texImporter/slidewiki.cfg";
			$slidewiki_config = file($slidewiki_config_path);
			
			// editing the config file
			$search_strings = array();
			array_push($search_strings, "\\\\Preamble");
			$positions = $this->lookForAString($slidewiki_config, $search_strings);
			
			if(array_key_exists("\\\\Preamble",$positions)) {
				// editing Preamble string for the different options
				$options = "html,png,hooks";
				$preamble = "\\Preamble"."{".$options."}\r\n";
				$slidewiki_config[$positions["\\\\Preamble"]] = $preamble;
			} else {
				die("Something wrong with slidewiki.cfg file for tex4ht configuration. Please, contact developers team.");
			}
			
			// save string to the file
			$slidewiki_config = implode($slidewiki_config);
			$fp = fopen("slidewiki.cfg", "w") or die("Couldn't open file"); 
			$numBytes = fwrite($fp, $slidewiki_config); 
			fclose($fp);
			
			// and run mk4ht script
			system("mk4ht htlatex $full_filename \"slidewiki\" > /dev/null ");
		}
		
		
	
		/*
		 *  Looking for a string (or an array of strings) in the file 
		 *  (file must be feed in as an array - by file() function)
         *  and return its position
		 *  Case insensitive search by preg_match() function
		 *  On fail - return false
		 */
		
		private function lookForAString($file, $strings) {
			$positions = array();
			foreach($file as $file_string_num => $file_string) {
				foreach($strings as $string_num => $string) {
					if(preg_match("#$string#i",$file_string)) {
						$positions[$string] = $file_string_num;
					}
				}
			}
			
			if(empty($positions)) {
				return false;
			} else {
				return $positions;
			}
		}
		
		/*
		 *  This function runs scripts, placed in /usr/share/tex4ht
		 *  You need to provide a file, derived from "ls /usr/share/tex4ht/" command
		 */
		
		private function runScripts() {
			// Running scripts from /usr/share/tex4ht folder on file
			// open scriptnames file
			/*$script_names_path = "../../../libraries/backend/texImporter/scriptnames";
			$script_names = file($script_names_path);
			
			$scripts = array();
			foreach($script_names as $string) {
				$array = preg_split("#\s+#",$string);
				foreach($array as $key => $element) {
					if($element == "") {
						unset($array[$key]); 
					} else {
						$array[$key] = '/usr/share/tex4ht/' . $array[$key];
					}
				}
				$scripts = array_merge($scripts, $array);
			}
			foreach($scripts as $script) {
				$script_name = preg_split("#/#", $script);
				$script_name = array_pop($script_name);
				system("$script $tex_filename >> $script_name.txt");
				copy("math.log", "$script_name.log");
			}*/
		}
		
		public function saveToDB($user_id, $slides) {			
			// create a deck with the name of a presentation
			$content = array ();
			$new_deck = new Deck ();
			$user = new User ();
			$user->createFromID ( $user_id );
			$new_deck->user = $user;
			$new_deck->comment = "new deck created!";
			$new_deck->title = '';
			$new_deck->create ();
			// get each slide and put it in the deck
			foreach($slides as $index => $slide) {
				$new_slide = new Slide ();
				$new_slide->user = $user;
				$new_slide->comment = "new slide created!";
				$slide_content = $slide;
				$new_slide->content = $slide_content;
				$new_slide->deck = $new_deck->id;
				$new_slide->position = $index + 1;
				$new_slide->create ();
				$content [] = $new_slide;
			}
			$new_deck->addContent ( $content );
			
			// save image to server
			// create image entries in the DB table
			return $new_deck->id;
		}
	}
?>