var languageCache = null;

function getLanguages(){
    gapi.client.setApiKey('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
        var request = gapi.client.request({
            path: '/language/translate/v2/languages',
            method: 'GET',
            params: {target :'en'}
           });
        var result = $.Deferred();        
        if(languageCache) {
            result.resolve(languageCache);
        } else {
            //TODO handle fail
            request.execute(function(msg) {
                languageCache = msg;                           
                result.resolve(msg);
            });
        }
        return result.promise();
}

function getLanguageName(language_id){
    gapi.client.setApiKey('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
    var task = getLanguages();
    var language_name = 'undefined';
    $.when(task).then(function(msg) {
        var languages = msg.data.languages;
        for (var i in languages){
            if (languages[i].language == language_id){
                language_name = languages[i].name;
            }
        }
    });
    return language_name;
}
function filterLatex(id){

    $.ajax({
        url : './?url=ajax/filterLatex&slide_id=' + id,
            success : function(msg){
//                msg = eval(msg);
//                alert(msg);
            }
    })
}
function remove_google(slide_id){
    var remove = confirm("I approve that the slide content was manually changed and it is not an automatically translated by Google Translate service anymore.");
    if (remove){
        $.ajax({
            url : './?url=ajax/remove_google&slide_id=' + slide_id,
            success : function(){
                $('#google_banner_'+slide_id).remove();
            }
        })
    }
}
function getLanguagesList(template){ 
    
    var task = getLanguages();
    var current = $('#current_language').attr('lang');    
        $.when(task).then(function(msg) {
            //alert(11);
            $('#languages').empty();
            $('#languages').append('<div class="close">x</div>'); 
            $(template).tmpl(msg.data).appendTo($('#languages'));            
            var current_td = $('#languages_to_translate').find('#'+current);
            current_td.addClass('current_lang');
            $('#languages').modal('show');
    });
}

function setDeckLanguage(lang_id,deck_id){
    var lang_name = getLanguageName(lang_id);
    var full_language = lang_id+'-'+lang_name;
    $.ajax({
        url : './?url=ajax/setDeckLanguage&language=' + full_language + '&deck_id=' + deck_id,
        success : function(){
            $('#current_language_null').hide();
            $('#current_language').attr('lang',lang_id).show();
            $('#current_language_name').empty().append('Language: '+lang_name);
            $('#languages').modal('hide');
        }
    })
}
function getAllTranslations(){
    gapi.client.setApiKey('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
    var selected_id=$('.jstree-clicked')[0].id;
    var selected_properties = getPropertiesFromId(selected_id);
    var id = 0;
    var slide_id = 0;
    if (selected_properties['type']=='slide'){
        slide_id = selected_properties['itemId'];
        id =  selected_properties['deckId'];
    }else{
        slide_id = 0
        id = selected_properties['itemId'];
    }
    $.ajax({
        url : './?url=ajax/getAllTranslations&id=' + id + '&slide_id=' + slide_id,
        success : function(msg){
            var data = eval( '(' + msg + ')' ); 
            var current = $('#current_language').attr('lang'); 
            var languages_set = new Object();
            languages_set.languages = data;            
            $('#languages_list').empty();
            
            if(selected_properties['type']=='deck'){
                $('#existed_translations_deck').tmpl(languages_set).appendTo($('#languages_list'));
            } else{
                $('#existed_translations_slide').tmpl(languages_set).appendTo($('#languages_list'));
            }            
            for (var i in data){
                if (data[i].language){
                    if (data[i].language['id']==current){
                        $('#existed').find($('#'+current)).addClass('current_lang');
                    }else {
                        $('#existed').find($('#'+data[i].language['id'])).addClass('existed_lang');
                    }  
                }              
            } 
            $('#language_list_trigger').addClass('open');
           
        }      
    })
}

function showParentChanges(rev_id){
    var answer = confirm('The source deck for the current translation was changed. Would you like to see the changes have been made?');
    if (answer){
       $.ajax({
            url : './?url=ajax/getTranslatedFromLast&id=' + rev_id,
            success : function(msg){
                var data = eval ('(' + msg + ')');
                $('#compare_revisions').find('.modal-body').empty().load('./?url=compare/compareParentTranslations&language_id='+ data.languages["id"] + '&language_name=' + data.languages["name"] + '&deck=' + data.decks[1] + '&compareTo=' + data.decks[0]);
                $('#compare_revisions').modal('show');            
            }
        }) 
    }    
}
function goToTranslation(last_revision_url){    
    window.location = './deck/' + last_revision_url;   
}
function updateTranslation(last_id, language_id, language_name){    
   var full_language = language_id+'-'+language_name;
   $.ajax({
                url : './?url=ajax/translate&language=' + full_language + '&id=' + last_id,
                success : function(msg){                  
                    msg = eval(msg);
                    if (msg){                        
                        window.location = './deck/' + msg.id + '_' + msg.slug_title; 
                    }else {
                        alert('The deck is already being translated into '+language_name+' by another user');
                    }                
                }
            })
}
function translateDeck(language){    
    var current = $('#current_language').attr('lang');
    var selected_id=$('.jstree-clicked')[0].id;
    var selected_properties = getPropertiesFromId(selected_id);
    var id = selected_properties['itemId'];
    if (selected_properties['type']=='slide'){
        alert('Please, select a deck');
    }else{
        if (language != current){
            var full_language = language+'-'+getLanguageName(language);
            $("#languages").hide();  
            $.ajax({
                url : './?url=ajax/translate&language=' + full_language + '&id=' + id,
                success : function(msg){                  
                    msg = eval(msg);
                    if (msg){                        
                        window.location = './deck/' + msg; 
                    }else {
                        alert('The deck is already being translated into '+getLanguageName(language)+' by another user');
                    }                
                }
            })  
        }  
    }         
}
function setLanguage(node,lang_id){
    var lang_name = getLanguageName(lang_id);

    $(node).find($('#language_id')).val(lang_id);
    $(node).find($('#language_name')).val(lang_name);
    $(node).find($('#language_visible')).empty().append(lang_name);
    $("#languages").hide();  
}




