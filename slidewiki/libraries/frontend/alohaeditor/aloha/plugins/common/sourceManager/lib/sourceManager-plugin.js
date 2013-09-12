define(
	['aloha/plugin', 
	'aloha/floatingmenu', 
	'i18n!aloha/nls/i18n', 
	'css!sourceManager/css/sourceManager.css'],
function( plugin, FloatingMenu, i18nCore) {
    "use strict";
 
    return plugin.create( 'sourceManager', {
        init: function() {
			var insertHTMLButton = new Aloha.ui.Button({
				'name': 'sourceManager',
				'iconClass': 'aloha-button-htmlSource',
				'size': 'small',
				'onclick': function(element, event) {
				//$('#html_source_code').val('');				 
				fill_source_code(0);
				hide_editor_toolbar();},
				'tooltip': 'Edit HTML code',
				'toggle': false
			});
			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertHTMLButton,
				i18nCore.t('Source Code'),
				1
			);			
        }
    });
});