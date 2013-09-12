<?php

	class ManipulationLib {
	
		public static function getFileName($uri) {
			$filename = ManipulationLib::getFilenameFromPath($uri);
			$full_filename = implode($filename, ".");
			$filename_ext = array_pop($filename);
			$short_filename = array_pop($filename);
			return array("full_filename" => $full_filename, "filename_ext" => $filename_ext, "short_filename" => $short_filename);
		}
		
		private static function getFilenameFromPath($uri) {
			$tex_filename = preg_split("#/#", $uri);
			$tex_filename = array_pop($tex_filename);
			$tex_filename = preg_split("#\.#", $tex_filename);
			return $tex_filename;
		}
		
		public static function unpackZipArchiveTo($short_filename, $filename_ext, $path) {
			$zip = new ZipArchive;
			$res = $zip->open("$short_filename" . "." . "$filename_ext");
			if ($res === TRUE) {
				if(!file_exists($path)) {
						mkdir("./" . $path);
				}
				$zip->extractTo("./$path/");
				$zip->close();
				return true;
			} else {
				die("Could not extract .zip archive.");
			}
		}
		
		public static function generateRandomName() {
			return uniqid();
		}
		
		/*
		 * Return the current date in DD-MM-YYYY format
		 * If month one digit - without trailing zero!
		 */		
		public static function getFormattedDate() {
			$date = getdate();
			$date_string = $date['mday'] . "-" . $date['mon'] . "-" . $date['year'];
			return $date_string;		
		}
	}
?>