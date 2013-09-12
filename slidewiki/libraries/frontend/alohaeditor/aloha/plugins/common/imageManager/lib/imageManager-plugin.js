define(
	['aloha', 'aloha/plugin', 'aloha/jquery', 'aloha/floatingmenu', 'i18n!format/nls/i18n', 'i18n!aloha/nls/i18n', 'aloha/console', 
	'css!imageManager/css/imageManager.css'],
function(Aloha, Plugin, jQuery, FloatingMenu, i18n, i18nCore, domToXhtml) {
    var
		GENTICS = window.GENTICS;
    "use strict";
 
    return Plugin.create( 'imageManager', {
        init: function() {
			var insertButton = new Aloha.ui.Button({
				'name': 'imageManager',
				'iconClass': 'aloha-button-imageManager',
				'size': 'small',
				'onclick': function(element, event) {init_img_manager();hide_editor_toolbar();},
				'tooltip': 'Insert Image',
				'toggle': false
			});
			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertButton,
				i18nCore.t('floatingmenu.tab.insert'),
				1
			);
			var insertSVGButton = new Aloha.ui.Button({
				'name': 'SVGManager',
				'iconClass': 'aloha-button-SVGManager',
				'size': 'small',
				'onclick': function(element, event) {var popup_window=window.open("libraries/frontend/svg-edit/svg-editor.html");},
				'tooltip': 'Design and Insert SVG Images',
				'toggle': false
			});
			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertSVGButton,
				i18nCore.t('floatingmenu.tab.insert'),
				1
			);
			var insertMathButton = new Aloha.ui.Button({
				'name': 'MathButton',
				'iconClass': 'aloha-button-MathJaxButton',
				'size': 'small',
				'onclick': function(element, event)  {$('#math_source_code').val('\\[ Your LaTeX Code here \\]');$('#math_source_preview').html('-');$("#modal_math_code").draggable({handle: ".modal-header"});$('#modal_math_code').modal('show');hide_editor_toolbar();},
				'tooltip': 'Insert LaTeX-based mathematic equations',
				'toggle': false
			});	 

			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertMathButton,
				i18nCore.t('floatingmenu.tab.insert'),
				1
			);
			var insertCodeButton = new Aloha.ui.Button({
				'name': 'CodeButton',
				'iconClass': 'aloha-button-CodeButton',
				'size': 'small',
				'onclick': function(element, event)  {$('#source_code_snippet').val('');$("#modal_code_snippet").draggable({handle: ".modal-header"});$('#modal_code_snippet').modal('show');hide_editor_toolbar();},
				'tooltip': 'Insert Code Snippets',
				'toggle': false
			});	 

			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertCodeButton,
				i18nCore.t('floatingmenu.tab.insert'),
				1
			);			
        }
    });
});