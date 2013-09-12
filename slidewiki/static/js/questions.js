




//-----------------------lists staff-----------------------------
// lists - manually created questionnaires




function getUserLists(id){    
    $.ajax({
        url:'./?url=ajax/getUserLists',
        async : false,
        success : function(msg) {
            var data = eval("("+msg+")");
            $("#listsTable").empty();            
            for (var i in data){
                $("#listsShow").tmpl(data[i]).appendTo("#listsTable");
            }
        }
    })
}
function addToList(list_id, quest_id){
    $.ajax({
        url : "./?url=ajax/addToList&quest_id=" + quest_id + "&list_id=" + list_id,
        success : function(){
            var node = $('#showList_'+quest_id);
            node.popover("hide");
        }
    });
}
function addList(quest_id){
    var title = $('#list_title').val();
    $.ajax({
        url : "./?url=ajax/addList&title=" + title,
        success : function(msg){
            if (quest_id){
                addToList(eval(msg), quest_id);
                showLists(quest_id);
            }            
        }
    })    
}
function showLists(quest_id){
    $.ajax({
        url : "./?url=ajax/getUserLists",
        success : function(msg) {
        var data = eval("(" + msg + ")");
        var lists = "";
        if (data.length){
            lists+="<ul id='lists'>";
            $.each(data, function(i, val) {
            	lists+= "<li><a style='cursor:pointer' id = '" + val.id + "' onClick='addToList(" + val.id + "," +quest_id + ")'>" + val.title + "</a></li>";
            });
            lists+="</ul>";
            lists+='<hr>';           
       }
       lists+='<input type="text" id="list_title" name="title" class="span3" value="" placeholder="Add new..."></input>'; 
       lists+='<button class="btn primary small" onclick="addList(' + quest_id + ')">Save</button>';
       var node = $('#showList_'+quest_id);
       node.popover({
            placement : "right",
            trigger : "manual"       
       });
       node.popover("show");       
       $("div.content").empty().append(lists);
       $("div.content").append('<div align="right"><a style="cursor:pointer" onclick="$(this).parent().parent().parent().parent().remove()">Close</a></div>');
       }
   });   
}
function listDelete(id){
   var answer = confirm('Do you really want to delete this list? This operation is irreversible');
   if (answer) {
        $.ajax({
            url : './?url=ajax/listDelete&id=' + id,
            success : function(msg) {
                var data = eval('(' + msg + ')');
                //alert('Your list "' + data.title + '" is deleted successfully!')
                $('#listString-'+id).remove();
            }
        });
   }   
}
function listRenameOperation(id){   
    var newTitle = $("#newTitle").val();
    $.ajax({
        url : './?url=ajax/listRename&id=' + id + '&newTitle=' + newTitle
    });       
   $("#list-" + id).empty().append(newTitle); 
   $('#listRenameClose').click();
}
function listRename(id){
    var lists='';
    lists+='<input type="text" id="newTitle" name="newTitle" class="span3" value="" placeholder="Add new title"></input>'; 
    lists+='<button class="btn primary small" onclick="listRenameOperation(' + id + ')">Save</button>';
    var node = $('#renameList_'+id);
    node.popover({
       placement : "right",
       trigger : "manual"       
    });
    node.popover("show");
    $("div.content").empty().append(lists);
    $("div.content").append('<div align="right"><a href="#" id="listRenameClose" onclick="$(this).parent().parent().parent().parent().remove()">Close</a></div>');
}
function listView(id){
    $.ajax({
        url : './?url=ajax/getListQuestions&id=' + id,
        success : function(msg){
            var data = eval('(' + msg + ')');
            var node = $('#viewList_'+id);
            node.popover({
                placement : "right",
                trigger : "manual"                
            });
            node.popover("show");
            $("div.inner").css("width", "auto");
            $("div.content").empty();
            for ( var i in data.questions ) {
                diff = data.questions[i].difficulty; 
                var diff_id = data.questions[i].id;
                var star_widht = diff*9;
                $("#diff_bar_"+diff_id).width(star_widht);
                data.questions[i].question = stripslashes(data.questions[i].question);
            }
            $("#list-content").tmpl(data).appendTo("div.content");
            resetGolbalVars();
            stars_live();
            
            $("div.content").append('<div align="right"><a href="#" onclick="$(this).parent().parent().parent().parent().remove()">Close</a></div>');
        }
    })  
}
function deleteFromList(list_id,quest_id){
   var answer=confirm("This operation is irreversible. Are you sure?");
   if (answer) {
        $.ajax({
            url: './?url=ajax/deleteFromList&list_id=' + list_id + '&quest_id=' + quest_id,
            success: function() {
                $("#questionString_"+quest_id).remove();
                getUserLists();
            }
       });
   }
}





//--------------------------------questions staff-----------------------------





function easyQuestion(container){
    var selected_id = $('.jstree-clicked')[0].id;    
    var id = getPropertiesFromId(selected_id);
    var slide_id = id['itemId'];
    var tag_array = ['P','DIV','LI','TABLE','TH','TR','TD'];     
    var text_array = new Array();
    filter(container,tag_array);    
  
    var resultArray = new Array();
           
    text_array = unWrap(container, resultArray);     
    $('#questionslink').click();
    addQuestion(slide_id,'');
    var node = $('#question_form');
    node.find($('#quest_text')).val(text_array[0]);
    text_array.shift();            
    var answers = new Array();            
    $.each(text_array, function(){                
        var answer_obj = new Object();
        answer_obj = {
            id : 0,
            answer : this,
            explanation : '',
            is_right : 'yes'
        }
        answers.push(answer_obj);
    })
    loadAnswers(answers);
}
function stars_live() {
	$('#diff').hover(function() {
		$('#diff_bar, #diff_hover').toggle();
	},
	function() {
		$('#diff_bar, #diff_hover').toggle();
	});
	$("#diff").mousemove(function(e){
		var margin_doc = $("#diff").offset();		
		var width_votes = e.pageX - margin_doc.left;
		var ceiled_votes = 	Math.ceil(width_votes/17);	
		$('#diff_hover').width(ceiled_votes*17);				
	});	
	$('#diff').click(function(e){
		var margin_doc = $("#diff").offset();		
		var width_votes = e.pageX - margin_doc.left;
		var user_votes = Math.ceil(width_votes/17);
		$("#difficulty").val(user_votes);
		$("#diff_bar").width(user_votes*17);
	});	
}

function showSlideQuestions(slide) {
	if (!reloadTabContent('Questions',slide,'qs'))
		return;
	$.ajax({
		url : './?url=ajax/showSlideQuestions&id=' + slide,
                async : false,
		success : function(msg) {
			var data = eval("(" + msg + ")");
                        $("#item_questions").tmpl(data).appendTo("#itemquestions");
                        for ( var i in data.questions.accepted ) {
                                diff = data.questions.accepted[i].difficulty; 
                                var diff_id = data.questions.accepted[i].id;
                                var star_widht = diff*9;
                                $("#diff_bar_"+diff_id).width(star_widht);
                        }
                        stars_live();
		$('#additional-elements').hide();
		}
	});
}
function copyQuestion(id){
    $.ajax({
        url : './?url=ajax/copyQuestion&id=' +id,
        async : false,
        success : function(msg) {
            renewQuestions();
            var id = eval(msg);
            loadQuestion(id);
        }
    })   
}

function deleteQuestion(id){
    var answer = confirm("Are you sure you want to COMPLETELY remove the question?");
    if (answer) {
        $.ajax({
            url : './?url=ajax/deleteQuestion&id=' +id,
            async : false,
            success : function(msg) {
                if (msg == -1){
                    renewQuestions();
                }             
            }
        })
    }
}

function showDeckQuestions(id) {
	$('#additional-elements').hide();
	if (!reloadTabContent('Questions',id,'qd'))
		return;
	$.ajax({
		url : './?url=ajax/showDeckQuestions&id=' + id,
                async : false,
		success : function(msg) {			
			var data = eval("(" + msg + ")");
                        $("#item_questions").tmpl(data).appendTo("#itemquestions");
                        for ( var i in data.questions.accepted ) {
                                diff = data.questions.accepted[i].difficulty; // итоговый ретинг
                                var diff_id = data.questions.accepted[i].id;
                                var star_widht = diff*9;
                                $("#diff_bar_"+diff_id).width(star_widht);
                        }
                        stars_live();
		}
	});
}
function addQuestion(item_id, quest_id) {	
	var user = getCurrenUserID(); // get the current user_id
	if (!user) {
		var user_alert = eval("("
				+ '{"alert_type":"warning","msg":"Please login or register to add questions!"}'
				+ ")");
		$("#itemquestions").html('');
		$("#user_alert").tmpl(user_alert).appendTo("#itemquestions");
		return;
	}		
	if (quest_id){
		if ($("#question_form").css("display") == "none") {
			$("#question_form").show();
		}
		loadQuestion(quest_id, item_id);		
	} else {
		renewQuestions();
	}
}
function getSlideSelection(){
    var selection = window.getSelection();
    
    if (selection){
        var theParent = selection.getRangeAt(0).cloneContents();
        var container = document.createElement("div");
        container.appendChild(theParent.cloneNode(true));
        return container;
    }else return 0;    
}
function checkSelectionForQuest(container){    
    $(container).children().each(function(){
        if ($(this).text()==''){
            $(this).remove();
        }
    })
}
$.fn.extend({
    immediateText: function() {
        var clon = $(this).clone(true);
        clon.children().each(function(){
            $(this).remove();
        })        
        return clon.text();
    }
});
function filter(container,tag_array){  
   $(container).children().each(function(){
       if (!in_array(this.tagName, tag_array)){            
            $(this).replaceWith($(this).html());
        }          
   })
   $(container).children().each(function(){
       filter(this,tag_array);
   })   
}
function loadQuestion(quest_id,item_id){
   if (!item_id){
       item_id='';
   }
    $.ajax({
        url : './?url=ajax/getQuestion&id=' + quest_id,
        async : false,			
        success : function(msg) {            
            var data = eval("(" + msg + ")");
            //renewQuestions();
            if ($("#question_form").css("display") == "none") {
                    $("#question_form").show();
            }            
            $("#add_edit_title").empty().html('Edit Question for the <a style="cursor:pointer" onclick="slideModal(' + item_id +')">slide</a>');
            $('#quest_text').attr("value", "");
            $('#difficulty').val("");
            $("#save_button").hide();
            
            $('#quest_text').attr("value", stripslashes(data.question));
            $('#difficulty').val(data.difficulty);
            if (data.diff_flag == 1 ) {
                $("#diff_star").empty().append("<div class='diff' style='cursor:default;' id='diff'><div class = 'diff_bar' id = 'diff_bar'></div><div class='diff_info'>Determined difficulty</div>");
            }
            var star_widht = data.difficulty*17;
            $("#diff_bar").width(star_widht);            
            showAnswersFields(quest_id);
            loadAnswers(data.answers);                 
         } 
    });
    showQuestionRevision(quest_id);
    location.hash = '#question_form';
    var selected_id = $('.jstree-clicked')[0].id;
    updateModeAddress(selected_id,"quest");
}
function slideModal(slide_id){
    $.ajax({
        url: './?url=ajax/getSlide&id='+slide_id,
        success: function(msg){
            var data = eval('(' + msg + ')');
            $('#slide_modal_body').empty().append(
                    "<hr><div><div class='deck-container' name='show_slide'>" +
                    "<div id='slide-area' name='slide_content'>" +
                    data.content +
                    "</div></div>"					
                );
            $('#slide_modal').modal('show');
        }
    })    
    
    //window.location = './?url=main/slide&slide=' + slide_id;
}
function loadAnswers(answers){
    var answer = "";
    var answer_id = '0';
    var button = '';
    var explanation = "";
    var is_right = "";
    var answer_count = answers.length;      
    $("#answers tbody").empty();
    if (answer_count>0) {
        $.each(answers, function(index) {            
            answer = stripslashes(answers[index].answer);
            answer_id = answers[index].id;
            explanation = stripslashes(answers[index].explanation);            
            if (answers[index].is_right == 'yes') {
                    is_right = "checked";
            } else {
                    is_right = "";
            }
            button = '<button name="delete_answer" class="btn small answerRow" onclick="removeAnswer($(this))">-</button>';
            $("#answers tbody").append('<tr id="' + answer_id + '"><td>' +
                '<textarea class="onerow" rows="1" name="answer_text">' + answer  +'</textarea></td>' +				
                '<td><input class= "answerRow" type="checkbox" name="is_right"' + is_right + '></td>' +
                '<td><textarea rows="1" class="onerow" name="explanation">'+ explanation  + '</textarea></td>' +
                '<td>' + button + '</td>' +
                '</tr>'
            );

        });					
    }
    for (var i = answer_count;i<2;i++){                
        addMoreAnswer();                
    }
    addMoreAnswer(); // empty string for new answer  
}
function reAssign(quest_id, new_slide_id){
    $.ajax({
        url : './?url=ajax/reAssign&quest_id='+quest_id+'&new_slide_id='+new_slide_id,
        success : function(){
            renewQuestions();
        }
    })
}
function showQuestionRevision(quest_id){
    $("#quest_rev").attr("quest_id", quest_id);
    $('#quest_rev').empty();
    $.ajax({
        url : './?url=ajax/getQuestionRevisions&id=' + quest_id,
        async : false,			
        success : function(msg) {
            var data = eval("(" + msg + ")");            
            $("#quest_revisions").tmpl(data).appendTo("#quest_rev");
            for ( var i in data.questions ) {
                diff = data.questions[i].difficulty; 
                var diff_id = data.questions[i].id;
                var star_widht = diff*9;
                $("#diff_bar_rev_"+diff_id).width(star_widht);
                if (diff_id!=quest_id){
                    $("#useQuestRev_"+diff_id).empty().append("<a style='cursor:pointer' onclick='useQuestRevision(" + diff_id + ")'>Reverse to this revision</a>")
                }
            }
        }
    })
}
function showAnswersFields(questionId) {
    $("#save_question").empty().append('<button class="btn primary" onclick="saveQuestion(\'\',' + questionId + ')">Save</button>');
}
function saveQuestion(id, quest_id) {
	var questionId;
	var confirm = false;
	if (quest_id) { //editing a question
		questionId=quest_id;
		confirm = confirmNewQuestion(quest_id);
	} 
	if (confirm!=-1) {//save question
		$.ajax({ 
			url : './?url=ajax/saveQuestion&id=' + id + '&quest_id=' + quest_id
				+ '&question='
				+ encodeURIComponent($('#questions-new-question').find('[name="quest_text"]').val())			
				+ '&difficulty='
				+ encodeURIComponent($('#questions-new-question').find('[name="difficulty"]').val()),
			async : false,
			success : function(msg) {
				questionId = eval(msg)-0;                                
                                saveAllAnswers(questionId,confirm);
			}
		});
	}        
	renewQuestions();
}
function addMoreAnswer(){	
	button = '<button name="delete_answer" class="btn small answerRow" onclick="removeAnswer($(this))">-</button>';
	$("#answers tbody").append('<tr id="0"><td>' +
		'<textarea rows="1" class="onerow" name="answer_text"/></td>' +				
		'<td><input class= "answerRow" type="checkbox" name="is_right"></td>' +	
		'<td><textarea rows="1" class="onerow" name="explanation"/></td>' +
		'<td>' + button + '</td>' +		
		'</tr>'
	);
}
function renewQuestions(){
	//to be able to reload the tab content
	$("#itemquestions").removeClass();	
    var selected_id = $('.jstree-clicked')[0].id;
    var id = getPropertiesFromId(selected_id);
    var item_id = id['itemId'];
    var item_type = id['type'];
    if (item_type=='slide'){
        showSlideQuestions(item_id);
    }else {
        showDeckQuestions(item_id);
    }
}
function confirmNewQuestion(questionId) {
	var res = -1;
	var answer = 0;
	$.ajax({
		async : false,
		url : './?url=ajax/checkCreatingNewQuestionRev&question=' + questionId,
		success : function(msg) {
			msg = eval(msg);
			if (!msg) {
				res = false;
			}else {
				var answer = confirm("This change will create a new revision for the question. Are you sure you want to do it?");
				if (answer) {
					res = true;
				} else {
					res = -1;					
				}
			}
		}
	});
	return res;
}
function saveAllAnswers(questionId,confirm){
    var e = '';
    if (confirm) { //save with the new question revision (based_on)

        e = $("#answers tbody tr");					
        e.each(function() {
                saveAnswer($(this), questionId);						
        });

    } else {		//simple save		

        var answerId;
        e = $("#answers tbody tr");                        
        e.each(function() {
                answerId = $(this).attr('id');                                
                if (answerId!='0')//if answer is not newly created
                        editAnswer(answerId);
                else saveAnswer($(this),questionId);									
        });
    }	
}
function saveAnswer(node,questionId){
	if (node.find('[name="answer_text"]').val() != '') {
		var is_right = "";	
		if (node.find('[name="is_right"]').prop("checked")==true)
			is_right = "yes";
		if (node.find('[name="is_right"]').prop("checked")==false)
			is_right = "no";
		$.ajax({
			url : './?url=ajax/saveAnswer&questionId=' + questionId
				+ '&answer='
				+ encodeURIComponent(node.find('[name="answer_text"]').val())
				+ '&is_right='
				+ is_right
				+ '&explanation='
				+ node.find('[name="explanation"]').val(),
			success : function(msg) {
				var data = eval("(" + msg + ")");
				node.removeAttr("id");
				node.attr("id" , data.id);                                
			}
		});
	}
}
function editAnswer(answerId){
	var is_right = "";
	if ($("#" + answerId).find('[name="is_right"]').prop("checked")==true)
		is_right = "yes";
	if ($("#" + answerId).find('[name="is_right"]').prop("checked")==false)
		is_right = "no";
	$.ajax({
		url : './?url=ajax/editAnswer&answerId=' + answerId
			+ '&answer='
			+ encodeURIComponent($("#" + answerId).find('[name="answer_text"]').val())
			+ '&is_right='
			+ is_right
			+ '&explanation='
			+ $("#" + answerId).find('[name="explanation"]').val()
	});
}
function removeAnswer(button) {
    var answerRow = button.parents().parents().first();
    var answerId = answerRow.attr("id");	
    if (answerId > 0){
        var conf_answer = confirm("This change will totally delete the answer. Are you sure you want to do it?");
        if (conf_answer) {
            $.ajax({
                url : './?url=ajax/getQuestionId&answerId=' + answerId,
                success : function(msg) {				
                    var questionId = eval(msg);
                    var confirm = confirmNewQuestion(questionId);
                    if (confirm!=-1){
                        if (confirm) { //delete with the new question revision (based_on)
                            answerRow.remove();
                            $.ajax({
                                url : './?url=ajax/saveQuestion&quest_id=' + questionId,
                                success : function(msg) {
                                    var quest_id = eval(msg);
                                    var e = $("#answers tbody tr");
                                    e.each(function() {
                                            saveAnswer($(this), quest_id);	
                                    });
                                    renewQuestions();
                                    loadQuestion(quest_id);
                                }
                            });
                        }else{ //simple delete
                            $.ajax({
                                url : './?url=ajax/removeAnswer&answerId=' + answerId,
                                success : function() {
                                    answerRow.remove();
                                    renewQuestions();
                                    loadQuestion(questionId);
                                }
                            })
                        }
                    }
                }
            });
        }
    }else {
        conf_answer = 0;
        if (answerRow.find('[name="answer_text"]').val() != "") {
            conf_answer = confirm("This change will totally delete the answer. Are you sure you want to do it?");			
        } else {
            conf_answer = 1;
        }
        if (conf_answer) {
            answerRow.remove();
        }
    }
}
function useQuestRevision(id){
    $.ajax({
        url : "./?url=ajax/useQuestRevision&id=" + id,
        success : function(msg){
            var id = eval(msg);
            renewQuestions();
            loadQuestion(id);
        }
    })
}
function toggleAnswers(id){
    $("#quest_rev").find($("#answers" + id)).toggle();
}





//-----------------------------------assesment staff---------------------------------






function startTest(){
    var node = $('#quest_preferences');
    var type = node.find('[name="test_type"]').val();
    var id = node.find('[name="test_id"]').val();
    var mode = node.find($("[name=mode]:checked")).val();    
    var limit = node.find('[name="limit"]').val();
    window.location="./?url=main/test&id="+id+"&type="+type+"&mode="+mode+"&limit="+limit;
}
function saveTest(){
    var attempt = $('#quests-area').attr('attempt');
    var node = $("#results");
    
        node.find($(".resultsDiv")).each(function(){
            saveModule($(this),attempt);
        });
    
    alert("The test results are saved successfully!");
    $("#saveTestButton").hide();
}
function saveModule(node,attempt) {
    var wiki_app = node.attr("wiki_app")-0;
    var dich = node.attr('dich')-0;
    var mtf = node.attr('mtf')-0;
    var ripkey = node.attr('ripkey')-0;
    var morgan = node.attr('morgan')-0;
    var max_points = node.attr("mod_max")-0;
    var deck_id = node.attr('id');
    $.ajax({
        url : "./?url=ajax/saveTest&deck_id=" + deck_id +
        "&attempt=" + attempt + 
        "&wiki_app=" + wiki_app +
        "&dich=" + dich +
        "&mtf=" + mtf +
        "&ripkey=" + ripkey +
        "&morgan=" + morgan +
        "&max_points=" + max_points,
        async : false
    })
}
function showResults(test, model){ 
        document.onblur = function(){ //unbind a controller in exam mode

        }
	$('#results').empty();	
	var node = $('#question-last #results');        
	buildQuestList(test, node);
        countList(test, node, model);
	countModule($('#results').find("div#" + test.item_id));
        showModel('wiki_app');
	$("#countTestButton").hide();
	$("#saveTestButton").show();
        $('#saveTestButton').click();
        $("#countModeButtons").show();        
        $(window).unbind('onblur');        
}
function showModel(model){
    var node = $('#question-last #results');
    node.find('[name = "points_ts"]').hide();
    node.find($("[model = "+model+"]")).show();
}
function countList(data,nodeQuest){
    var quest_points = '';
    var quest_diff = '';
    var div = nodeQuest.find("#" + data.item_id);
    var string = '';
    for (var i in data.questions) {
        quest_diff = parseFloat(data.questions[i].difficulty).toFixed(2);
        string = div.find("[name='" + data.questions[i].id + "_string']");
        var model_div = string.find($(".points_ts"));
        model_div.children().each(function(){
            var model = $(this).attr('model');
            quest_points = countQuestion(data.questions[i].id, model);
            var pointsToShow = (quest_points/quest_diff * 100).toFixed(2);
            $(this).empty().append(pointsToShow + '%');
        });
    }
    for (i in data.modules){            
        countList(data.modules[i], div);
    }
}
function gotoQuestion(question_link){
    var adress = window.location.href;
    var adress_array = adress.split('#');
    window.location = adress_array[0] + question_link;    
}
function buildQuestList(data, nodeQuest){
    var attempt = $('#quests-area').attr('attempt');
    $('#results').show();
    $("#module").tmpl(data).appendTo(nodeQuest);
    var div = nodeQuest.find("#" + data.item_id);
    $('#questions_script').tmpl(data).appendTo(div.find("table tbody"));    
    for (var i in data.questions) {
        countAllModels(data.questions[i].id, attempt);
    }				
    for (var i in data.modules){        
        buildQuestList(data.modules[i], div);
    }
}
function countModule(node){
    var quest_points = 0;
    var now = 0;
    node.attr("mod_max",0);
    node.attr("wiki_app",0);
    node.attr("dich",0);
    node.attr("mtf",0);
    node.attr("morgan",0);
    node.attr("ripkey",0);
    if (node.find("tr.question_string").length){
        node.find("tr.question_string").each(function() {		
            var quest_diff = parseFloat($(this).find("[name='diff']").html());
            now = parseFloat(node.attr("mod_max"));
            node.attr("mod_max", parseFloat(now+quest_diff));
            var points_tab = $(this).find("[name='points_ts']");
            points_tab.each(function(){
                quest_points = parseFloat($(this).html()) * quest_diff / 100;
                var model = $(this).attr('model');
                now = parseFloat(node.attr(model));                
                node.attr(model, parseFloat(now+quest_points));
            })                
            
        });
        var max_points = parseFloat(node.attr("mod_max"));
        var wiki_app = parseFloat(node.attr("wiki_app"));
        var dich = parseFloat(node.attr("dich"));
        var mtf = parseFloat(node.attr("mtf"));
        var morgan = parseFloat(node.attr("morgan"));
        var ripkey = parseFloat(node.attr("ripkey"));
        var maxForUser = parseFloat(node.attr("maxForUser"));
        node.find('[name="module_total"]').remove();
        node.append(
                "<div name='module_total'><table><tr name='total_string' bgcolor='pink'><td style='text-align: right !important'><b>Total:</b><td>" 
                +"Guess-based:" + ((wiki_app/max_points)*100).toFixed(2) + "%"
                + "</td><td>Dichotomous:" +((dich/max_points)*100).toFixed(2)+ "%</td><td>MTF:" +((mtf/max_points)*100).toFixed(2)+ "%</td><td>Morgan:" +((morgan/max_points)*100).toFixed(2)+ "%</td><td>Ripkey:" +((ripkey/max_points)*100).toFixed(2)+ "%</td><td>your best is: " + (maxForUser*100).toFixed(2) + "%</td></tr></table></div>"
        );
        node.children("[name='moduleDiv']").each(function() {
            countModule($(this));
        });
    }else{
        node.remove();
    } 
}
function collapse(id){
    $('#'+id+' tr').each(function(){
        if($(this).attr('name')!='total_string'){
            $(this).hide();
        }
    }) 
}
function expand(id){
    $('#'+id+' tr').each(function(){
        $(this).show();        
    })
}
function countAllModels(quest_id, attempt){ 
  var node = $("#quests-area").find("[name='question-" + quest_id + "']");        
  var points = 0;
  var answer_check = '';
  var difficulty = node.find('[name="question_diff"]').val()-0;
  var count_correct = 0;
  var answer_div = node.find('[name="answer_div"]');
  var all = answer_div.length;
  var count_correct_points = 0;
  var mtf_points = 0;
  var checked = answer_div.find('[name="answer_points"]:checked');
  var checked_string ='';
  answer_div.each(function(){
      if ($(this).find('[name="answer_points"]').prop("checked")==true) {          
          checked_string+= $(this).attr('id')+',';
       }
  })  
  $.ajax({
      url : './?url=ajax/saveQuestResults&quest_id=' + quest_id + '&attempt=' + attempt + '&checked=' + checked_string 
  })
  var ripkey_result = false;
  var morgan_points = 0;
  var dich_points = difficulty;
  var penalty = 0;
  if (checked.length >= 0){        
    $.ajax({
        url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
        async : false,
        success : function(msg) {
            var data = eval ( '(' + msg + ')' );
            var correct = data.length;
            for (var i in data) {				
                answer_check = node.find('#' + data[i].id);               
                if (answer_check.find('[name="answer_points"]').prop("checked")==true) {
                    count_correct_points+=difficulty/data.length;                    
                    mtf_points+=difficulty/all;
                }
                if (answer_check.find('[name="answer_points"]').prop("checked")==false) {
                    dich_points =  0;	
                }
                answer_check.addClass("correctAnswer");
            }
            if (checked.length > correct){                    
                dich_points = 0;
                ripkey_result = false ;
                penalty = (difficulty*((checked.length - correct)/(all - correct))).toFixed(2);
            }
            else {
                ripkey_result = true;
                penalty = 0;
            }            
            
        count_correct = parseFloat(count_correct_points).toFixed(2);
        morgan_points = count_correct;            
        mtf_points = parseFloat(mtf_points).toFixed(2);
        dich_points = parseInt(dich_points);
        }
    });
    $.ajax({
        url : './?url=ajax/getDistractors&quest_id=' + quest_id,
        async : false,
        success : function(msg) {
            var data = eval ( '(' + msg + ')' );
            for (var i in data) {

                answer_check = node.find('#' + data[i].id);               
                if (answer_check.find('[name="answer_points"]').prop("checked")==true) {
                    morgan_points-=difficulty/data.length;	
                }					
                if (answer_check.find('[name="answer_points"]').prop("checked")==false) {
                    mtf_points = mtf_points - 0 + difficulty/all;
                }								
            }
        mtf_points = parseFloat(mtf_points).toFixed(2);
        morgan_points = parseFloat(morgan_points).toFixed(2);
        }
    });
  }
  //-----------MTF------------
  points = parseFloat(mtf_points).toFixed(2);       
  node.find('[name="question_points_mtf"]').val(points);
  //---------Ripkey-----------
  if (!ripkey_result) {
     points = 0;
  }else{
     points = parseFloat(count_correct).toFixed(2); 
  }   
  node.find('[name="question_points_ripkey"]').val(points);
  //------------Morgan---------
  if ( morgan_points < 0 ) points = 0; else points = morgan_points;
  node.find('[name="question_points_morgan"]').val(points);
  //---------Dich---------
  points = dich_points;       
  node.find('[name="question_points_dich"]').val(points);
  //-----------WikiApp-------
  if (count_correct <= 0){
     points = 0; 
  }else{
     points = parseFloat(count_correct - penalty).toFixed(2); 
  }
  node.find('[name="question_points"]').val(points);
  $.ajax({
    url : "./?url=ajax/countQuestionCorrect&quest_id=" + quest_id + "&points=" + points
  })  
}
function countQuestion(quest_id, model){
    var node = $("#quests-area").find("[name='question-" + quest_id + "']");        
    var points = 0;	
    if (node.find('[name="was_viewed"]').val()=="yes") {//if slide was viewed
        showAnswersWithoutConfirm(quest_id);
        return 0;
    }        
    switch (model){
        case 'mtf' :
            points = node.find('[name="question_points_mtf"]').val();
            break;
        case 'ripkey' :
            points = node.find('[name="question_points_ripkey"]').val();                        
            break;    
        case 'morgan' :
            points = node.find('[name="question_points_morgan"]').val();
            break;
        case 'dich' :
            points = node.find('[name="question_points_dich"]').val(); 
            break;
        case 'wiki_app' :
            points = node.find('[name="question_points"]').val();            
            break;
        default :
            points = node.find('[name="question_points"]').val();            
    }    
    return points;		
}
function showAnswersWithoutConfirm(quest_id){
    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
    var answer_check = "";
    $.ajax({
        url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
        success : function(msg) {
            var data = eval ( '(' + msg + ')' );			
            for (var i in data) {				
                answer_check = node.find('#' + data[i].id);
                answer_check.addClass("correctAnswer");					
            }
        }
    });
    showExplanation(quest_id);    
    node.find("[name='showAnswersButton']").remove();
}
function showAnswers(quest_id) {
    var show = false;
    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
    if (node.find('[name="was_viewed"]').val()=="no"){
        show = confirm("After this you will not get points for this question. Are you sure?");
    }else {
        show = true;
    }        
    if (show) {        
        node.find('[name="was_viewed"]').val("yes");
        showAnswersWithoutConfirm(quest_id);
    }
}
function showAutoSlide(slide_basic_id, deck_id, quest_id){
    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
    var show = false;
    if (node.find('[name="was_viewed"]').val()=="no"){
        show = confirm("After this you will not get points for this question. Are you sure?");
    }else {
        show = true;
    }  
    if (show) {
        $.ajax({
            url : './?url=ajax/getSlideByBasic&basic_id=' + slide_basic_id + '&deck_id=' + deck_id,
            success : function(msg){
                var slide = eval( "(" + msg + ")" );
                var slide_content = slide.content;
                node.append(
                    "<hr><div><div class='deck-container' name='show_slide'>" +
                    "<div id='slide-area' name='slide_content'>" +
                    slide_content +
                    "</div></div>"					
                );
                MathJax.Hub.Queue(['Typeset',MathJax.Hub,'slide-area']);
                node.find("[name='showSlideButton']").remove();
                node.find('[name="was_viewed"]').val("yes");
            }
        });
    }
}
function showListSlide(slide_basic_id, quest_id){
    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
    var show = false;
    if (node.find($(".correctAnswer")).size()==0 && node.find("[name='slide_content']").size()==0){
        show = confirm("After this you will not get points for this question. Are you sure?");
    }else {
        show = true;
    }
    if (show) {
        $.ajax({
            url : './?url=ajax/getLastSlideByBasic&basic_id=' + slide_basic_id,
            success : function(msg){
                var slide = eval( "(" + msg + ")" );
                var slide_content = slide;
                node.append(
                    "<hr><div><div class='deck-container' name='show_slide'>" +
                    "<div id='slide-area' name='slide_content'>" +
                    slide_content +
                    "</div></div>"					
                );
                MathJax.Hub.Queue(['Typeset',MathJax.Hub,'slide-area']);
                node.find("[name='showSlideButton']").remove();                
            }
        });
    }
}
function showExplanation(quest_id) {
    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
    if (node.find($(".explanation")).size()==0){
        var answer_check = "";
        var explanation = "";
        node.find("[name = 'answer_div']").each(function (){
            answerId = $(this).attr("id");
            $.ajax({
                url : './?url=ajax/getAnswer&answerId=' + answerId,
                async: false,
                success : function(msg) {
                    var data = eval( "(" + msg + ")" );
                    explanation = stripslashes(data.explanation);	
                    if (explanation != '') {
                        answer_check = node.find('#' + answerId);                                        
                        answer_check.append("<div class='explanation'>" + explanation + "</div>");
                    }
                }
            });		
        })
    }
}
function tryAgain(item_id,type){
    window.location="./?url=main/tests&id=" + item_id + "&type=" + type;   
}
function changeType(id){
    var checked = $('#only_user').prop('checked');
    if(checked){
        loadTestsByType('user', id);
    }else{
        loadTestsByType('manual', id);
    }
}
function ExamMode(){
    var checked = $('#exam').prop('checked');
    var node = $('#quest_preferences');
    if(checked){
        node.find('[name="test_type"]').val('exam');
    }else{
        node.find('[name="test_type"]').val('auto');
    }
}
function loadTestsByType(type, id){    
    if (type!='0'){
        var options='';
        var selected='';
        $.ajax({
            url : './?url=ajax/getTestsByType&type=' + type,
            success : function(msg){
                var data = eval('(' + msg + ')');
                if (data.length){
                    for (var i in data){
                        if (data[i].id == id) {
                            selected = 'selected';
                        }else {
                            selected = '';
                        }
                        options+='<option value="' + data[i].id + '" ' + selected +'>' + data[i].title + ' (' + data[i].quest_count +')</option>'; 
                    }
                    $('#choose_manual').show();
                    $('#attention_manual').hide();
                    $('#test_type').empty().append(options);
                    $('#testDiv').show();
                    $('#modeDiv').show();
                    $("#limitDiv").show();
                    $('#go_button').show();
                } else {
                    if (type=='user'){
                        loadTestsByType('manual',id);
                        $('#only_user_div').hide();
                    }else{
                        $('#choose_manual').hide();
                        $('#attention_manual').show();
                        $('#test_type').empty().append('<option value="">There are no tests of the chosen type</option>');
                        $('#modeDiv').hide();
                        $("#limitDiv").hide();
                        $('#go_button').hide();
                    }
                }                
            }
        })
    }else{
        $('#modeDiv').hide();
        $("#limitDiv").hide();
        $('#testDiv').hide();
    }
}
function changeModeDescription(mode){
    var description='';
    switch (mode) {        
        case '1' :
            description = "In the <b>random</b> mode you will be asked to answer the questions from all the modules of the chosen course in random order.";
            break;
        case '2' :
            description = "In the <b>increase difficulty</b> mode you will be asked to answer the questions from all the modules of the chosen course" +
                " in order of increasing difficulty";
            break;
        case '3' :
            description = "In the <b>only difficult</b> mode you will be asked to answer only the questions with more than average difficulty from all the modules" +
                " in random order";
            break; 
        default :
            description = "There are several possible modes:<br/>" +
                "In the <b>random</b> mode you will be asked to answer the questions from all the modules of the chosen course" +
                "in random order.<br/>" +
                "In the <b>increase difficulty</b> mode you will be asked to answer the questions from all the modules of the chosen course" +
                " in order of increasing difficulty<br/>" +
                "In the <b>only difficult</b> mode you will be asked to answer only the questions with more than average difficulty from all the modules" +
                " in random order<br/>";
    }
        $("#mode_description").empty().append(description);
    if(mode>0){        
        $("#limitDiv").show();
    }else {
        $("#limitDiv").hide();
    }
}
function changeTestDescription(id){
    if(id>0){
        var type = $("#quest_preferences").find('[name="test_type"]').val();
        $.ajax({
            url : './?url=ajax/getTestDescription&type=' + type + '&id=' + id,
            success : function(msg){
                $('#modeDiv').show();
                var data = eval( "(" + msg + ")" );
                var user_name=''
                if (data.user){
                    user_name = data.user.username;
                }
                var count = data.quest_count;
                var max_for_user = data.max_for_user;
                if (!max_for_user){max_for_user = '-'}
                var description = '';
                if(user_name){
                    description+= 'Author: ' + user_name +'<br/>';
                }
                $('#test_description').empty().append( description + 
                    'Number of questions: ' + count + 
                    '<br/>Your maximal result: ' + max_for_user);
            }
        })
    }else {
        $('#modeDiv').hide();
        $("#limitDiv").hide();
    }
}




//------------------------------------not in use for now

//function saveExamResults(){
//    var node = $("#results");
//    var main_div = node.find($(".resultsDiv")).first();
//    var points = main_div.attr("wiki_app")-0;
//    var dich = main_div.attr('dich')-0;
//    var mtf = main_div.attr('mtf')-0;
//    var ripkey = main_div.attr('ripkey')-0;
//    var morgan = main_div.attr('morgan')-0;
//    var max_points = main_div.attr("mod_max")-0;
//    var item_id = main_div.attr('id');
//    $.ajax({
//        url : "./?url=ajax/saveTest&type=exam&item_id=" + item_id + "&points=" + points +
//        "&dich=" + dich +
//        "&mtf=" + mtf +
//        "&ripkey=" + ripkey +
//        "&morgan=" + morgan +
//        "&max_points=" + max_points,
//        async : false
//    })
//}


//function countDichotomous(quest_id){    
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");           
//    var answer_check = "";
//    var difficulty = node.find('[name="question_diff"]').val();
//    var points = difficulty;
//    var answer_div = node.find('[name="answer_div"]');
//    var checked = answer_div.find('[name="answer_points"]:checked');
//    if (checked.length == 0){
//        points = 0;
//    }else {
//        $.ajax({
//            url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
//            async : false,
//            success : function(msg) {
//                var data = eval ( '(' + msg + ')' ); 
//                var correct = data.length;
//                if (checked.length > correct){                    
//                    points = 0;
//                }else {
//                    for (var i in data) {
//                        answer_check = node.find('#' + data[i].id);                            
//                        if (answer_check.find('[name="answer_points"]').prop("checked")==false) {
//                            points =  0;	
//                        }                             
//                    } 
//                }                                
//            }
//        });
//    }
//    points = parseFloat(points).toFixed(2);
//    return points;
//}
//function countMorganCorrect(quest_id){
//    var points = 0;
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
//    var difficulty = node.find('[name="question_diff"]').val()-0;
//    var answer_check = "";
//    $.ajax({
//        url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
//        async : false,
//        success : function(msg) {
//            var data = eval ( '(' + msg + ')' );
//            for (var i in data) {				
//                answer_check = node.find('#' + data[i].id);               
//                if (answer_check.find('[name="answer_points"]').prop("checked")==true) {
//                    points+=difficulty/data.length;	
//                }								
//            }                                                            
//        }
//    });
//    points = parseFloat(points).toFixed(2);
//    return points;
//}
//function countMTF(quest_id){
//    var points = 0;
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
//    var difficulty = node.find('[name="question_diff"]').val()-0;
//    var answer_div = node.find('[name="answer_div"]');
//    var all = answer_div.length;
//    var answer_check = "";
//    $.ajax({
//        url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
//        async : false,
//        success : function(msg) {
//            var data = eval ( '(' + msg + ')' );
//            for (var i in data) {				
//                answer_check = node.find('#' + data[i].id);               
//                if (answer_check.find('[name="answer_points"]').prop("checked")==true) {
//                    points+=difficulty/all;	
//                }								
//            }
//            $.ajax({
//                url : './?url=ajax/getDistractors&quest_id=' + quest_id,
//                async : false,
//                success : function(msg) {
//                    var data = eval ( '(' + msg + ')' );
//                    for (var i in data) {				
//                        answer_check = node.find('#' + data[i].id);               
//                        if (answer_check.find('[name="answer_points"]').prop("checked")==false) {
//                            points+=difficulty/all;	
//                        }								
//                    }
//                }
//            });
//            points = parseFloat(points).toFixed(2);                                                
//        }
//    });
//    return points;
//}
//function countCorrect(quest_id) {
//    
//    var points = 0;
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
//    var difficulty = node.find('[name="question_diff"]').val()-0;
//    var answer_check = "";
//    $.ajax({
//        url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
//        async : false,
//        success : function(msg) {
//            var data = eval ( '(' + msg + ')' );
//            for (var i in data) {				
//                answer_check = node.find('#' + data[i].id);               
//                if (answer_check.find('[name="answer_points"]').prop("checked")==true) {
//                    points+=difficulty/data.length;	
//                }								
//            }
//            points = parseFloat(points).toFixed(2);                                                
//        }
//    });
//    return points;
//}
//function countMorganIncorrect(quest_id){
//    var points = 0;
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");    
//    var difficulty = node.find('[name="question_diff"]').val()-0;
//    var answer_check = "";
//      $.ajax({
//      url : './?url=ajax/getDistractors&quest_id=' + quest_id,
//      async : false,
//      success : function(msg) {
//          var data = eval ( '(' + msg + ')' );
//          for (var i in data) {				
//                answer_check = node.find('#' + data[i].id);               
//                if (answer_check.find('[name="answer_points"]').prop("checked")==true) {
//                    points+=difficulty/data.length;	
//                }								
//            }
//            points = parseFloat(points).toFixed(2);    
//      }
//    });       
//    return points;
//}
//function countRipkeyIncorrect(quest_id){
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
//    var answer_div = node.find('[name="answer_div"]');
//    var checked = answer_div.find('[name="answer_points"]:checked');
//    var result = false;
//      $.ajax({
//      url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
//      async : false,
//      success : function(msg) {
//          var data = eval ( '(' + msg + ')' );
//          var correct = data.length;
//          var ch = checked.size();
//          if (ch <= correct) {
//              result = true;              
//          }else {
//              result = false ;       
//          }          
//      }
//    });
//    return result;
//}
//function countIncorrect(quest_id){
//    var penalty = 0;
//    var node = $("#quests-area").find("[name='question-" + quest_id + "']");
//    var answer_div = node.find('[name="answer_div"]');
//    var difficulty = node.find('[name="question_diff"]').val()-0;
//    var checked = answer_div.find('[name="answer_points"]:checked');
//      $.ajax({
//      url : './?url=ajax/getCorrectAnswers&quest_id=' + quest_id,
//      async : false,
//      success : function(msg) {
//          var data = eval ( '(' + msg + ')' );
//          var correct = data.length;
//          var all = answer_div.size();
//          var ch = checked.size();
//          if (ch <= correct) {
//              penalty=0;              
//          }else {
//              penalty = (difficulty*((ch - correct)/(all - correct))).toFixed(2);              
//          }          
//      }
//    });
//    return penalty;
//}


//function openDoubtfulModal(id){	
//    $('#doubtful_button').bind('click', function() {
//        markDoubtfulQuestion(id);
//        }		
//    );
//    $('#doubtful_modal').modal({
//        keyboard : true,
//        backdrop: true,
//        show: true
//    })	
//}
//function markDoubtfulQuestion(id){
//    $.ajax({
//        url : './?url=ajax/markDoubtfulQuestion&id=' + id
//            + '&comment=' 
//            + encodeURIComponent($('#doubtful_modal').find('[name="doubtful_comment"]').val()),
//        success : function() {
//            $('#doubtful_comment').attr('value', '');
//            $('#doubtful_button').unbind('click');
//            $('#doubtful_modal').modal('hide');
//            $('#questionslink').click();
//        }
//    });
//}
//function unMarkDoubtful(id) {
//	$.ajax({
//		url : './?url=ajax/unMarkDoubtful&id=' + id,
//		success : function() {			
//			$('#questionslink').click();
//		}
//	});
//}