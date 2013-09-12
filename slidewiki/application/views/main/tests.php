<script src="static/js/questions.js"></script>
<?php if ($user['is_authorized']) :?>
<form class="form-stacked" id="quest_preferences">
    <fieldset>
        <?php if ($type=='manual' || $type=='user') :?>
            <legend>Set your preferences</legend>
            <div class="clearfix">
                <div data-alert="alert" class="alert alert-block alert-error" style="display:none">
                    <div class="input" style="margin-top:5px">
                        <script type="text/javascript">
                            $(document).ready(function(){
                                var test_type = $("#quest_preferences").find("[name='test_type']").val();
                                var selected = <?php echo $id; ?>;
                                loadTestsByType(test_type, selected);
                            })
                        </script>                        
                        <select name="test_type" onchange="loadTestsByType(this.value, <?php echo $id; ?>)">
                            <option value="manual" <?php if ($type=='manual') echo 'selected'; ?>>User's test</option>
                            <option value="user" <?php if ($type=='user') echo 'selected'; ?>>Own test</option>
                        </select>
                    </div>                    
                </div>
                <div data-alert="alert" id="testDiv" class="alert alert-block alert-info" style="display:<?php if ($type=='') echo 'none'; else echo 'block';?>">
                    <div style="float:left" id="choose_manual">
                        <legend>Choose a test:</legend>
                        <div class="input" style="margin-top:5px; margin-left:5px; float:left;">
                            <select id="test_type" name="test_id">
                                <?php if($id > 0) echo('<option value="'.$id.'" selected>'.$title.'</option>')?>
                            </select>                            
                        </div>
                        <div style="width:10px;float:left;">
                            &nbsp;
                        </div>
                        <div id="only_user_div" style="float:left; margin-top:10px; padding-right: 10px;">
                            <label>
                                <input type="checkbox" id="only_user" onchange="changeType(<?php echo $id; ?>)">
                                <span>Show only my tests</span>
                            </label>
                        </div>
                    </div>
                    <div id="attention_manual" style="display:none; float:left">
                        There are no tests for the chosen preferences.
                    </div>                    
                    <div>
                        <div style="padding:20px; overflow:hidden;" id="test_description"></div>
                    </div>
                </div>
            <?php else : ?>
                <legend>Set your preferences for the "<?php echo $title;?>" test</legend>
                <div class="clearfix">
                    <div data-alert="alert" class="alert alert-block alert-error" style="display:none;">
                        <div class="input" style="margin-top:5px">
                            <select name="test_type">
                                <option value="auto" <?php if ($type=='auto' || $type=='') echo 'selected'; ?>>Automatic test</option>
                                <option value="exam" <?php if ($type=='exam') echo 'selected'; ?>>Exam mode</option>
                            </select>
                        </div>
                    </div>                    
                    <div data-alert="alert" id="testDiv" class="alert alert-block alert-info" style="display:none">
                        <div class="input" style="margin-top:5px">
                            <select id="test_type" name="test_id">
                                <?php if($id > 0) echo('<option value="'.$id.'" selected>'.$title.'</option>')?>
                            </select>
                        </div>               
                    </div>                
            <?php endif;?>
                    <div data-alert="alert" id="modeDiv" class="alert alert-block" style="display:<?php if ($type=='' || $id<=0) echo 'none'; else echo 'block';?>">
                       
                            <legend>Choose a mode:</legend>
                            <div class="input">
                                <label>
                                    <input type="radio" name="mode" value="1" checked>
                                    <span>random: you will be asked to answer the questions from all the modules of the chosen course
                                    in random order.</span>
                                </label>
                                <label>
                                    <input type="radio" name="mode" value="2">
                                    <span>increase difficulty: you will be asked to answer the questions from all the modules of the chosen course
                                    in order of increasing difficulty</span>
                                </label>
                                <label>
                                    <input type="radio" name="mode" value="3">
                                    <span>only difficult: you will be asked to answer only the questions with more than average difficulty from all the modules
                                    in random order</span>
                                </label>
                            </div>
                            <?php if ($type == 'auto' || $type == 'exam') :?>
                                <div id="exam_div" style="margin-top:10px; padding-right: 10px;">
                                    <label>
                                        <input type="checkbox" id="exam" <?php if ($type == 'exam') :?> checked <?php endif; ?> onchange="ExamMode()">
                                        <span>Examination mode</span>
                                    </label>
                                </div>
                            <?php endif;?>                      

                    </div>
                    <div data-alert="alert" id="limitDiv" class="alert alert-block alert-success" style="display:<?php if ($type=='' || $id<=0) echo 'none'; else echo 'block';?>">

                        <legend>Set up the maximal number of questions from each module:</legend>
                        <div class="input" style="margin-top:5px">
                            <input name="limit" type="edit" value="<?php echo $limit;?>">
                            </input>
                        </div>

                        
                    </div>
               </div>       
        </fieldset>
    <div class="actions" id="go_button" style="<?php if ($id) : ?>display:block<?php else: ?>display:none<?php endif;?>">
        <a onclick="startTest()" class="btn primary">GO!</a>
    </div>
</form>

<?php else:?>
<div>Please, Login or Register for taking a test!</div>
<?php endif; ?>

