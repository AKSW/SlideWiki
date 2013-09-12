
<script src="libraries/frontend/deck.js/modernizr.custom.js"></script>
        <script src="libraries/frontend/deck.js/core/deck.core.js"></script>
        <script src="libraries/frontend/deck.js/extensions/menu/deck.menu.js"></script>
        <script src="libraries/frontend/deck.js/extensions/goto/deck.goto.js"></script>
        <script src="libraries/frontend/deck.js/extensions/status/deck.status.js"></script>
        <script src="libraries/frontend/deck.js/extensions/navigation/deck.navigation.js"></script>
        <script src="libraries/frontend/deck.js/extensions/hash/deck.hash.js"></script>       
<script src="static/js/view-spec/playq.js"></script>
<script src="libraries/frontend/deck.js/extensions/scale/deck.scale.questions.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<script src="static/js/questions.js"></script>
<script src="static/js/view-spec/playq.js"></script>


<!--<script src="static/js/scale.js"></script>

<title><?php echo $test_title; ?></title>
<!--</head>-->

<script type="text/javascript">
    var json_obj = <?php echo $test; ?>;
</script>

<?php if ($type=="exam") :?>
<script>
            document.onblur = function () {
                alert("Sorry, I've lost your responses. Please, do not leave the test window.");
                window.location = "./?url=main/test&id=<?php echo $test_id;?>&type=<?php echo $type;?>&mode=<?php echo $mode;?>&limit=<?php echo $limit;?>";
            }
</script>

<header class="page-header">
	
	<span id="deck_title"><h1>Test for "<?php echo $test_title;?>" course </h1> 
        </span> 
	
</header>

<section class="deck-container deck-single">
    <article name="quests-area" id="quests-area" test_sum="0" max_sum="0" attempt="<?php echo $attempt?>">
        <?php 
        $i = 0;
        
        foreach ($questions as $question) { 
            $i++;
            
            ?>				
            <div class="slide" name="question-<?php echo $question->id; ?>" id="question-<?php echo $question->id; ?>-<?php echo $question->module['id']; ?>">	
                <h3 class="module_name" name="<?php echo $question->module['name']; ?>">Module "<?php echo $question->module['name']; ?>"</h3>
                <form class="form-stacked">
                    <fieldset>
                        <legend><h3>Question <?php echo $i;?> of <?php echo $count;?></h3></legend>
                        <span id="question_text" name="question_text"><?php echo $question->question; ?></span>							
                        <input type="hidden" name="question_points" id="question_points" value="0">
                        <input type="hidden" name="question_points_mtf" id="question_points_mtf" value="0">
                        <input type="hidden" name="question_points_ripkey" id="question_points_ripkey" value="0">
                        <input type="hidden" name="question_points_morgan" id="question_points_morgan" value="0">
                        <input type="hidden" name="question_points_dich" id="question_points_dich" value="0">
                        <input type="hidden" name="question_diff" id="question_diff" value="<?php echo $question->difficulty?>">
                        <input type="hidden" name="is_counted" value="no">
                        <input type="hidden" name="was_viewed" value="no">
                        <div class="clearfix">
                            <div class="input">
                                <ul class="inputs-list">
                                    <?php 
                                    $sh_answers = $question->answers;
                                    shuffle($sh_answers);
                                    foreach ($sh_answers as $answer) { ?>														
                                        <li>
                                            <div class="clearfix" id = "<?php echo $answer['id']?>" name="answer_div">
                                                <label>
                                                    <input type="checkbox" name="answer_points">
                                                    <span><?php echo $answer['answer']; ?></span>
                                                </label>
                                            </div>
                                        </li>
                                    <?php }?>
                                </ul>
                            </div>
                        </div>
                    </fieldset>                    
                </form>
            </div>

        <?php echo PHP_EOL; ?>
        <?php } ?>
		
		
        <div class="slide" name="question-last" id="question-last">
                <a class="btn small primary" id="countTestButton" onclick="showResults(json_obj)">View the results</a>
                <?php if($user['is_authorized']) : ?>
                <a class="btn small primary" id="saveTestButton" style="display: none;" onclick="saveTest()">Save the results</a>
   

                <?php endif; ?>
                <div id="countModeButtons" style="display:none;">
                <a class="btn small primary" id="wiki_app" onclick="showModel('wiki_app')">Guessing-based scoring</a> 
                <a class="btn small primary" id="continious" onclick="showModel('dich')">Dichotomous scoring</a>
                <a class="btn small primary" id="old_model" onclick="showModel('morgan')">Morgan algorythm</a>
                <a class="btn small primary" id="moodle" onclick="showModel('mtf')">MTF scoring</a>
                <a class="btn small primary" id="moodle" onclick="showModel('ripkey')">Ripkey scoring</a>
                </div>
                
                <div id="results" style="display:none;">
                    
                </div>
        </div>
    </article>
	
    <footer>
        <nav>
            <a href="#" class="deck-prev-link" title="Previous">&#8592;</a>
            <a href="#" class="deck-next-link" title="Next">&#8594;</a>


            <form action="." method="get" class="goto-form">
                <label for="goto-slide">Go to question:</label>
                <input type="number" name="slidenum" id="goto-slide">
                <input type="submit" value="Go">
            </form>
        </nav>
    </footer>
</section>


<?php else : ?>
<header class="page-header">
	
	<span id="deck_title"><h1>Test for "<?php echo $test_title;?>" course </h1> 
            <?php if ($type!='list') :?>
            <h4><a href="./deck/<?php  
             echo $test_id . '_' . $slug_title; ?>">Go to the presentation</a></h4>
            <?php endif; ?>
        </span> 
	
</header>

<section class="deck-container deck-single">
    <article name="quests-area" id="quests-area" test_sum="0" max_sum="0" attempt="<?php echo $attempt?>">
        <?php 
        $i = 0;
        
        foreach ($questions as $question) { 
            $i++;
            
            ?>				
            <div class="slide" name="question-<?php echo $question->id; ?>" id="question-<?php echo $question->id; ?>-<?php echo $question->module['id']; ?>">	
                <h3 class="module_name" name="<?php echo $question->module['name']; ?>">Module "<?php echo $question->module['name']; ?>"</h3>
                <form class="form-stacked">
                    <fieldset>
                        <legend><h3>Question <?php echo $i;?> of <?php echo $count;?></h3></legend>
                        <span id="question_text" name="question_text"><?php echo stripslashes($question->question); ?></span>							
                        <input type="hidden" name="question_points" id="question_points" value="0">
                        <input type="hidden" name="question_points_mtf" id="question_points_mtf" value="0">
                        <input type="hidden" name="question_points_ripkey" id="question_points_ripkey" value="0">
                        <input type="hidden" name="question_points_morgan" id="question_points_morgan" value="0">
                        <input type="hidden" name="question_points_dich" id="question_points_dich" value="0">
                        <input type="hidden" name="question_diff" id="question_diff" value="<?php echo $question->difficulty?>">
                        <input type="hidden" name="is_counted" value="no">
                        <input type="hidden" name="was_viewed" value="no">
                        <div class="clearfix">
                            <div class="input">
                                <ul class="inputs-list">
                                    <?php 
                                    $sh_answers = $question->answers;
                                    shuffle($sh_answers);
                                    foreach ($sh_answers as $answer) { ?>														
                                        <li>
                                            <div class="clearfix" id = "<?php echo $answer['id']?>" name="answer_div">
                                                <label>
                                                    <input type="checkbox" name="answer_points">
                                                    <span><?php echo stripslashes($answer['answer']); ?></span>
                                                </label>
                                            </div>
                                        </li>
                                    <?php }?>
                                </ul>
                            </div>
                        </div>
                    </fieldset>

                    <div class="actions">
                        <a class="btn small primary" name="showAnswersButton" onclick="showAnswers(<?php echo $question->id; ?>)">Show answers</a>
                        <?php if($type=='auto') :?> 
                        <a class="btn small primary" name="showSlideButton" onclick="showAutoSlide(<?php echo $question->item_id?>,<?php echo $question->module['id'] ?>,<?php echo $question->id; ?>)">Show slide</a>
                        <?php else : ?>
                        <a class="btn small primary" name="showSlideButton" onclick="showListSlide(<?php echo $question->item_id?>,<?php echo $question->id; ?>)">Show slide</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        <?php echo PHP_EOL; ?>
        <?php } ?>
		
		
        <div class="slide" name="question-last" id="question-last">
                <a class="btn small primary" id="countTestButton" onclick="showResults(json_obj)">View the results</a>
                <?php if($user['is_authorized']) : ?>
                <a class="btn small primary" id="saveTestButton" style="display: none;" onclick="saveTest()">Save the results</a>
   

                <?php endif; ?>
                <a class="btn small primary" id="tryAgainButton" onclick="tryAgain(<?php echo $test_id ?>,'<?php if ($type=='list'): echo 'user'; else: echo 'auto'; endif; ?>')">Try it again!</a>
                
                <div id="countModeButtons" style="display:none;">
                <a class="btn small primary" id="wiki_app" onclick="showModel('wiki_app')">Guessing-based scoring</a> 
                <a class="btn small primary" id="continious" onclick="showModel('dich')">Dichotomous scoring</a>
                <a class="btn small primary" id="old_model" onclick="showModel('morgan')">Morgan algorythm</a>
                <a class="btn small primary" id="moodle" onclick="showModel('mtf')">MTF scoring</a>
                <a class="btn small primary" id="moodle" onclick="showModel('ripkey')">Ripkey scoring</a>
                </div>
                
                <div id="results" style="display:none;">
                    
                </div>
        </div>
    </article>
	
    <footer>
        <nav>
            <a href="#" class="deck-prev-link" title="Previous">&#8592;</a>
            <a href="#" class="deck-next-link" title="Next">&#8594;</a>


            <form action="." method="get" class="goto-form">
                <label for="goto-slide">Go to question:</label>
                <input type="number" name="slidenum" id="goto-slide">
                <input type="submit" value="Go">
            </form>
        </nav>
    </footer>
</section>
<?php endif;?>

<div id="results"></div>
<script id="module" type="text/x-jquery-tmpl">
    <div class= 'resultsDiv' name = 'moduleDiv' wiki_app=0 dich=0 mtf=0 morgan=0 ripkey=0 wiki_app=0 maxForUser=${max_for_user} id=${item_id}>
        <div style="float:left; width: 50%"><h4>${title}</h4></div><div style="text-align:right;">[<a style="cursor: pointer" onclick="collapse(${item_id})">Collapse</a>] [<a style="cursor: pointer" onclick="expand(${item_id})">Expand</a>]</div>
        <table>
            <thead>            
            <th>Question</th>
            <th>Difficulty</th>
            <th style='display:none'>Not displayed</th>
            <th>Points</th>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>   
</script>
<script id="questions_script" type="text/x-jquery-tmpl">
    {{each questions}}
        <tr name='${$value.id}_string' class="question_string">           
            <td>
                <a style="cursor:pointer;" onclick="gotoQuestion('#question-${id}-${$data.item_id}')">${question}</a>
            </td>
            <td name='diff'>${difficulty}</td>
            <td name='points' style='display:none'></td>
            <td class='points_ts'>
                <div name='points_ts' model='wiki_app' style='display:none'></div>
                <div name='points_ts' model='dich' style='display:none'></div>
                <div name='points_ts' model='morgan' style='display:none'></div>
                <div name='points_ts' model='ripkey' style='display:none'></div>
                <div name='points_ts' model='mtf' style='display:none'></div>
            </td>
        </tr>        
    {{/each}}
</script>
