define(
	['aloha', 'aloha/plugin', 'aloha/jquery', 'aloha/floatingmenu', 'i18n!format/nls/i18n', 'i18n!aloha/nls/i18n', 'aloha/console', 
	'css!easyQuestion/css/easyQuestion.css'],
    
function(Aloha, Plugin, jQuery, FloatingMenu, i18n, i18nCore, domToXhtml) {
    "use strict";
 
    return Plugin.create( 'easyQuestion', {
        init: function() {
                            var insertQuestButton = new Aloha.ui.Button({
				'name': 'easyQuestion',
				'iconClass': 'aloha-button-easyQuestion',
				'size': 'small',
				'onclick': function() {                          
                                    var selection = Aloha.getSelection();
                                    var theParent = selection.getRangeAt(0).cloneContents();
                                    var container = document.createElement("div");
                                    container.appendChild(theParent.cloneNode(true));
                                    if($(container).text()!=''){
                                        checkSelectionForQuest(container);
                                        easyQuestion(container);
                                    }else {
                                        alert('Select a fragment of the slide first');
                                    }
                                    return false;
                                },
				'tooltip': 'Create a question from selected',
				'toggle': false
			});
			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertQuestButton,
				i18nCore.t('Easy Quest'),
				1
			);		
        }
    });
});