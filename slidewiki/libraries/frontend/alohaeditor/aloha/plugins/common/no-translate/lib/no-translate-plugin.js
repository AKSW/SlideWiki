define(
	['aloha', 'aloha/plugin', 'aloha/jquery', 'aloha/floatingmenu', 'i18n!format/nls/i18n', 'i18n!aloha/nls/i18n', 'aloha/console', 
	'css!no-translate/css/no-translate.css'],
function(Aloha, Plugin, jQuery, FloatingMenu, i18n, i18nCore, domToXhtml) {
    var
		GENTICS = window.GENTICS;
    "use strict";
 
    return Plugin.create( 'no-translate', {
        init: function() {
			var insertNoTranslateButton = new Aloha.ui.Button({
				'name': 'no-translate',
				'iconClass': 'aloha-button-no-translate',
				'size': 'small',
				'onclick': function() {
                                    var 
                                    markup = jQuery('<span class="no-translate"></span>'),
                                    rangeObject = Aloha.Selection.rangeObject,
                                    foundMarkup,
                                    selectedCells = jQuery('.aloha-cell-selected');
                                    
                                    // formating workaround for table plugin
                                    if ( selectedCells.length > 0 ) {
                                            var cellMarkupCounter = 0;
                                            selectedCells.each( function () {
                                                    var cellContent = jQuery(this).find('div'),
                                                            cellMarkup = cellContent.find(button);

                                                    if ( cellMarkup.length > 0 ) {
                                                            // unwrap all found markup text
                                                            // <td><b>text</b> foo <b>bar</b></td>
                                                            // and wrap the whole contents of the <td> into <b> tags
                                                            // <td><b>text foo bar</b></td>
                                                            cellMarkup.contents().unwrap();
                                                            cellMarkupCounter++;
                                                    }
                                                    cellContent.contents().wrap('<'+button+'></'+button+'>');
                                            });

                                            // remove all markup if all cells have markup
                                            if ( cellMarkupCounter == selectedCells.length ) {
                                                    selectedCells.find(button).contents().unwrap();
                                            }
                                            return false;
                                    }
                                    // formating workaround for table plugin

                                    // check whether the markup is found in the range (at the start of the range)
                                    foundMarkup = rangeObject.findMarkup(function() {
                                            return this.nodeName.toLowerCase() == markup.get(0).nodeName.toLowerCase();
                                    }, Aloha.activeEditable.obj);

                                    if (foundMarkup) {
                                            // remove the markup
                                            if (rangeObject.isCollapsed()) {
                                                    // when the range is collapsed, we remove exactly the one DOM element
                                                    GENTICS.Utils.Dom.removeFromDOM(foundMarkup, rangeObject, true);
                                            } else {
                                                    // the range is not collapsed, so we remove the markup from the range
                                                    GENTICS.Utils.Dom.removeMarkup(rangeObject, markup, Aloha.activeEditable.obj);
                                            }
                                    } else {
                                            // when the range is collapsed, extend it to a word
                                            if (rangeObject.isCollapsed()) {
                                                    GENTICS.Utils.Dom.extendToWord(rangeObject);
                                            }

                                            // add the markup
                                            GENTICS.Utils.Dom.addMarkup(rangeObject, markup);
                                    }
                                    // select the modified range
                                    rangeObject.select();
                                    return false;
                                },
				'tooltip': 'No-translate',
				'toggle': false
			});
			FloatingMenu.addButton(
				'Aloha.continuoustext',
				insertNoTranslateButton,
				i18nCore.t('floatingmenu.tab.format'),
				1
			);			
        }
    });
});