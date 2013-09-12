<?php

require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'utils'. DS .'util.php';

class ImgController extends Controller {

	function imgmanager() {
		$this->_template->disableHeader();
		$this->_template->disableFooter();
		
		// looking for the images this user owns
		$media = new Media();
		$images = $media->getImagesFor($this->_user['id']);
		
		$this->set ( 'images', $images );
	}
        
        function upload(){
        	if(isset($_POST['your_link']) && $_POST['your_link']!=''){
        		$postf=split('\.',$_POST['your_link']);
        		$tmp=split('\/',$_POST['your_link']);
        		$user_id = $_SESSION['uid'];
        		$media = new Media();
        		$name=$tmp[count($tmp)-1];
                $new_id = $media->saveImagetoDB($user_id, '',$name );
        	    $folder = './upload/media/images/' . $user_id . '/'; 
                if(!file_exists($folder)) {
                        mkdir($folder);
                 }
				$filename = './upload/media/images/' . $user_id . '/'.$new_id.'.'.$postf[count($postf)-1];
				file_put_contents($filename, file_get_contents($_POST['your_link']));
				$img_size = Util::extractOriginalSizeOf($filename);
                $media->updateSizeOf($new_id, $img_size);
                $media->updateURIOf($new_id, $filename);
                //Form a js-file  
                $res = '<script type="text/javascript">';
                $res .= "var data = new Object;";
                $res .= 'data.src="'. $filename.'";';
                $res .= 'data.name="'. $name.'";';
                $res .= 'window.parent.handleResponse(data);';
                $res .= "</script>";
                echo $res;
        	}
            if (isset($_FILES['uploaded_img'])){
                
                $whitelist = array(".gif", ".jpeg", ".png", ".jpg");        
                $data = array();
                $error = true;

                //check the extensions
                foreach  ($whitelist as  $item) {
                    if(preg_match("/$item\$/i",$_FILES['uploaded_img']['name'])) $error = false;
                }

                //if no errors, upload the file
                if(!$error) {

                    $folder = './upload/media/images/' . $_SESSION['uid'] . '/'; 
                    if(!file_exists($folder)) {
                        mkdir($folder);
                    }
		    

                   if(is_uploaded_file($_FILES['uploaded_img']['tmp_name'])){
                       
                        $name_array = explode('.',basename( $_FILES['uploaded_img']['name']));
                        $title = $name_array[0];
                        $user_id = $_SESSION['uid'];
                        $media = new Media();
                        $new_id = $media->saveImagetoDB($user_id, '', $title);
                        $uploadedFile = $folder . $new_id . '.' . $name_array[1];
                        if(move_uploaded_file($_FILES['uploaded_img']['tmp_name'],$uploadedFile)){
                            
                            $data = $_FILES['uploaded_img'];
                            $data['src'] = $uploadedFile;
                            $img_size = Util::extractOriginalSizeOf($uploadedFile);
                            echo $img_size;
                            $media->updateSizeOf($new_id, $img_size);
                            $media->updateURIOf($new_id, $uploadedFile);
                        }
                        else {   
                            $data['errors'] = "An error occured";
                        }
                    }
                    else {   
                        $data['errors'] = "File was not uploaded";
                    }
                }
                else{

                    $data['errors'] = 'The file format is not supported';
                }


                //Form a js-file  
                $res = '<script type="text/javascript">';
                $res .= "var data = new Object;";
                foreach($data as $key => $value){
                    $res .= 'data.'.$key.' = "'.$value.'";';
                }
                $res .= 'window.parent.handleResponse(data);';
                $res .= "</script>";

                echo $res;
                
            }
        }       
	
}