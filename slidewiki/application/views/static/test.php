<?php


    echo '<h2>List of questions for <a href="./deck/'.$test->item_id . '_' . $test->sluggify($test->title) . '">'.$test->title.'</a> course</h3>';
    $questions = $test->questions;
    if (count($questions)){
        echo '<ol>';
        for ($i=0; $i<count($questions); $i++){ 
            //print_r($question);
            $module = $questions[$i]->module['name'];
            $slug_module = $test->sluggify($module);
            if ($i>0 && $questions[$i]->module['name'] != $questions[$i-1]->module['name']){
                 echo '<h4>Module <a href="./deck/'.$questions[$i]->module['id']. '_' . $slug_module . '">"'.$module.'"</a></h4>';
            }       
            echo '<li>'.$questions[$i]->question;
            echo ' (by <a href="./user/'.$questions[$i]->user->id.'">'.$questions[$i]->user->username.'</a>)<br/>';
           
            foreach ($questions[$i]->answers as $answer){             
                
                if ($answer['is_right'] == 'yes'){
                    echo '&#9745;  ';
                }else {
                    echo '&#9744;  ';
                }                
                echo $answer['answer'].'<br/>';
            }
        echo '</li>';
        } 
        echo '</ol>';
    }   


?>
