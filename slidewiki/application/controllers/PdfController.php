<?php
require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'html2pdf' . DS . 'html2pdf.class.php');

class PdfController extends Controller {
    function test(){
        $item_id=$_GET['id'];  
        if (isset($_GET['show_answers']))
            $show_answers = $_GET['show_answers'];
        else 
            $show_answers = 2;
        $test = new Test();
        $test->createFromItem($item_id);
        $test->questions = $test->getAllQuestions();

        $content= '';
        
        $content.= '<h2>List of questions for '.$test->title.' course</h2>';
        $questions = $test->questions;
        if (count($questions)){
            $content.= '<ol>';
            for ($i=0; $i<count($questions); $i++){
                $content.= '<li>'.$questions[$i]->question.'<br/>';
                if ($show_answers){
                   foreach ($questions[$i]->answers as $answer){ 
                        if ($show_answers == '2'){
                            if ($answer['is_right'] == 'yes'){
                                    $content.= '&#9745;&nbsp;&nbsp;&nbsp;';
                                }else{
                                    $content.= '&#9744;&nbsp;&nbsp;&nbsp;';                        
                                } 
                            }else{
                                $content.= '&#9744;&nbsp;&nbsp;&nbsp;';
                            }                      
                            $content.= $answer['answer'].'<br/>';
                        } 
                }                
                $content.= '</li>';
            } 
            $content.= '</ol>';
        }
        
        $content = '<page style="font-family: freeserif"><br />'.$content.'</page>';
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        //$html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($content);
        $html2pdf->Output('utf8.pdf');

    } 
}

