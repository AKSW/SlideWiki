//     (c) 2011 Michael Aufreiter
//     Proper is freely distributable under the MIT license.
//     For all details and documentation:
//     http://github.com/michael/proper

// Goals:
//
// * Annotations (strong, em, code, link) are exclusive. No text can be both
//   emphasized and strong.
// * The output is semantic, valid HTML.
// * Cross-browser compatibility: Support the most recent versions of Chrome,
//   Safari, Firefox and Internet Explorer. Proper should behave the same on
//   all these platforms (if possible).
//
// Proper uses contenteditable to support these features. Unfortunately, every
// browser handles contenteditable differently, which is why many
// browser-specific workarounds are required.



moving_flag=0;
function enable_moving(){
	//make images draggable and resizable
	if(!moving_flag){
	$( ".slide-body img" ).resizable().parent('.ui-wrapper').draggable();	
	moving_flag=1;
	}else{
	$( ".slide-body img" ).resizable('destroy').parent('.ui-wrapper').draggable('destroy');	
	moving_flag=0;
	}
}
function get_selected_text(){
	var str = (window.getSelection) ? window.getSelection() : document.selection.createRange();
	str = str.text || str;
	str = str + ''; // the best way to make object a string...
	return str;
}
(function(){
  
  // _.Events (borrowed from Backbone.js)
  // ------------------------------------
  
  // A module that can be mixed in to *any object* in order to provide it with
  // custom events. You may `bind` or `unbind` a callback function to an event;
  // `trigger`-ing an event fires all callbacks in succession.
  //
  //     var object = {};
  //     _.extend(object, Backbone.Events);
  //     object.bind('expand', function(){ alert('expanded'); });
  //     object.trigger('expand');
  //
  
  _.Events = window.Backbone ? Backbone.Events : {

    // Bind an event, specified by a string name, `ev`, to a `callback` function.
    // Passing `"all"` will bind the callback to all events fired.
    bind : function(ev, callback) {
      var calls = this._callbacks || (this._callbacks = {});
      var list  = this._callbacks[ev] || (this._callbacks[ev] = []);
      list.push(callback);
      return this;
    },

    // Remove one or many callbacks. If `callback` is null, removes all
    // callbacks for the event. If `ev` is null, removes all bound callbacks
    // for all events.
    unbind : function(ev, callback) {
      var calls;
      if (!ev) {
        this._callbacks = {};
      } else if (calls = this._callbacks) {
        if (!callback) {
          calls[ev] = [];
        } else {
          var list = calls[ev];
          if (!list) return this;
          for (var i = 0, l = list.length; i < l; i++) {
            if (callback === list[i]) {
              list.splice(i, 1);
              break;
            }
          }
        }
      }
      return this;
    },

    // Trigger an event, firing all bound callbacks. Callbacks are passed the
    // same arguments as `trigger` is, apart from the event name.
    // Listening for `"all"` passes the true event name as the first argument.
    trigger : function(ev) {
      var list, calls, i, l;
      if (!(calls = this._callbacks)) return this;
      if (list = calls[ev]) {
        for (i = 0, l = list.length; i < l; i++) {
          list[i].apply(this, Array.prototype.slice.call(arguments, 1));
        }
      }
      if (list = calls['all']) {
        for (i = 0, l = list.length; i < l; i++) {
          list[i].apply(this, arguments);
        }
      }
      return this;
    }
  };
  
  _.stripTags = function(input, allowed) {
  // Strips HTML and PHP tags from a string
  //
  // version: 1009.2513
  // discuss at: http://phpjs.org/functions/strip_tags
     allowed = (((allowed || "") + "")
        .toLowerCase()
        .match(/<[a-z][a-z0-9]*>/g) || [])
        .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
     var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
         commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
     return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1){
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
     });
  };

  // Initial Setup
  // -------------
  controlsTpl = ' \
	    <div class="proper-commands"> \
	      <br class="clear"/>\
	    </div>';
/*
  controlsTpl = ' \
    <div class="proper-commands btn-group"> \
      <a href="#" title="Undo (CTRL+Z)" class="btn undo" command="undo"> <i class="icon-step-backward"></i></a> \
      <a href="#" title="Redo (CTRL+Y)" class="btn redo" command="redo"> <i class="icon-step-forward"></i></a> \
      <a href="#" title="Remove Format (CTRL+SHIFT+R)" class="btn removeFormat" command="removeFormat"> <i class="icon-minus"></i></a> \
      <a href="#" title="Justify Left" class="btn justifyLeft" command="justifyLeft"> <i class="icon-align-left"></i></a> \
      <a href="#" title="Justify Center" class="btn justifyCenter" command="justifyCenter"><i class="icon-align-center"></i></a> \
      <a href="#" title="Justify Right" class="btn justifyRight" command="justifyRight"><i class="icon-align-right"></i></a> \
      <a href="#" title="H5 (CTRL+SHIFT+5)" class="btn h5" command="h5"><b>H<sub>5</sub></b></a> \
      <a href="#" title="H4 (CTRL+SHIFT+4)" class="btn h4" command="h4"><b>H<sub>4</sub></b></a> \
      <a href="#" title="H3 (CTRL+SHIFT+3)" class="btn h3" command="h3"><b>H<sub>3</sub></b></a> \
      <a href="#" title="Strong (CTRL+SHIFT+S)" class="btn strong" command="strong"><i class="icon-bold"></i></a> \
      <a href="#" title="Emphasis (CTRL+SHIFT+E)" class="btn em" command="em"><i class="icon-italic"></i></a> \
      <a href="#" title="Insert Image (CTRL+SHIFT+I)" class="btn image" command="image"><i class="icon-picture"></i></a> \
      <a href="#" id="image_manager" title="Image Manager" class="btn init_img_manager" command="init_img_manager"><i class="icon-folder-open"></i><i class="icon-picture"></i></a> \
      <a href="#" title="Insert Link (CTRL+SHIFT+L)" class="btn link" command="link"><i class="icon-bookmark"></i></a> \
      <a href="#" title="Bullet List (CTRL+SHIFT+B)" class="btn ul" command="ul"><i class="icon-list"></i></a> \
      <a href="#" title="Numbered List (CTRL+SHIFT+N)" class="btn ol" command="ol"><i class="icon-list-alt"></i></a> \
      <a href="#" title="Outdent (SHIFT+TAB)" class="btn outdent" command="outdent"><i class="icon-indent-left"></i></a> \
      <a href="#" title="Indent (TAB)" class="btn indent" command="indent"><i class="icon-indent-right"></i></a> \
      <a href="#" title="Inline Code (CTRL+SHIFT+C)" class="btn code" command="code"><i class="icon-barcode"></i></a> \
      <a href="#" title="Enable/Disable Moving images" class="btn moving" command="moving"><i class="icon-move"></i></a> \
      <a href="#" title="Quote (CTRL+SHIFT+Q)" class="btn quote" command="quote"><b>&ldquo;Q&rdquo;</b></a> \
      <a href="#" id="set_color" title="Set Color" class="btn forecolor dropdown-toggle" command="forecolor" ><input id="color-picker" type="text" name="color1" value="#333399" /><span class="caret"></span></a>\
      <a href="#" title="HTML Code (CTRL+SHIFT+H)" class="btn htmlCode" command="htmlCode">HTML</a> \
      <a href="#" title="Fullscreen (CTRL+SHIFT+F)" class="command fullscreen" command="fullscreen"><div>Fullscreen</div></a>\
      <br class="clear"/>\
    </div>';
  */
  // Proper
  // ------
  this.Proper = function(options) {
    var activeElement = null, // element that's being edited
        $controls,
        events = _.extend({}, _.Events),
        pendingChange = false,
        options = {},
        defaultOptions = { // default options
          multiline: true,
          markup: true,
          placeholder: 'Enter Text',
          codeFontFamily: 'Monaco, Consolas, "Lucida Console", monospace'
        };
    
    
    // Commands
    // --------
    
    function exec(cmd) {       
      var command = commands[cmd];
      if (command.exec) {
        command.exec();
      } else {
        if (command.isActive()) {
          command.toggleOff();
        } else {
          command.toggleOn();
        }
      }
	 	//apply scaling
	 	var whole_id=$("#active_editor_id").text();
	 	var parts = whole_id.split("_");
	 	var selected_id=parts[2];
	 	if(parts[1]=='title' || parts[1]=='body'){
	 		applyScaling(selected_id)	
	 	}  
    }

    function removeFormat() {
      document.execCommand('removeFormat', false, true);
      _.each(['em', 'strong', 'code'], function (cmd) {
        var command = commands[cmd];
        if (command.isActive()) {
          command.toggleOff();
        }
      });
    }

    // Give code elements (= monospace font) the class `proper-code`.
    function addCodeClasses() {
      $(activeElement).find('font').addClass('proper-code');
    }

    var nbsp = $('<span>&nbsp;</span>').text();

    var commands = {
     init_img_manager: {
		 exec: function() {
		     init_img_manager();
		 }
	 },
	 save:{
    	 exec: function() {
    		 save_changes();
    	 }
     },
	 svg:{
    	 exec: function() {
    		 var popup_window = window.open("libraries/frontend/svg-edit/svg-editor.html");
    	 }
     },
	 moving:{
    	 exec: function() {
    		 enable_moving();
    	 }
     },     
	 redo:{
    	 exec: function() {
    		 document.execCommand('redo', false, false);
    	 }
     },
	 undo:{
    	 exec: function() {
    		 document.execCommand('undo', false, false);
    	 }
     },     
     htmlCode:{
    	 exec: function() {
    		 $('#html_source_code').val($.trim($('#'+$('#active_editor_id').text()).html()));
    		 $('#modal_html_code').modal('show');
    	 }
     },     
     fullscreen:{
    	 exec: function() {
    		 edit_fullscreen();
    	 } 
     },
     removeFormat: {
         exec: function() {
             removeFormat();
             document.execCommand('formatBlock', false, "<P>");
           }
     },
     forecolor:{
         exec: function() {
             //removeFormat();
             document.execCommand('foreColor', false, $('#color_value').val());
           } 
     },
     h5: {
         exec: function() {
             removeFormat();
             document.execCommand('formatBlock', false, "<H5>");
           }
     },
     h4: {
         exec: function() {
             removeFormat();
             document.execCommand('formatBlock', false, "<H4>");
           }
     },
     h3: {
         exec: function() {
             removeFormat();
             document.execCommand('formatBlock', false, "<H3>");
           }
     },
      em: {
        isActive: function() {
          return document.queryCommandState('italic', false, true);
        },
        toggleOn: function() {
          //removeFormat();
          document.execCommand('italic', false, true);
        },
        toggleOff: function() {
          document.execCommand('italic', false, true);
        }
      },

      strong: {
        isActive: function() {
          return document.queryCommandState('bold', false, true);
        },
        toggleOn: function() {
          removeFormat();
          document.execCommand('bold', false, true);
        },
        toggleOff: function () {
          document.execCommand('bold', false, true);
        }
      },
      quote:{
          exec: function() {
              removeFormat();
              document.execCommand('formatBlock', false, "<blockquote>");
            }    	  
      },
      code: {
        isActive: function() {
            return false;

        },
        toggleOn: function() {
          removeFormat();
          document.execCommand('formatBlock', false, "<PRE>");
          //addCodeClasses();
        },
        toggleOff: function () {
          var sel;
          if ($.browser.webkit && (sel = saveSelection()).collapsed) {
            // Workaround for Webkit. Without this, the user wouldn't be
            // able to disable <code> when there's no selection.
            var container = sel.endContainer
            ,   offset = sel.endOffset;
            container.data = container.data.slice(0, offset)
                           + nbsp
                           + container.data.slice(offset);
            var newSel = document.createRange();
            newSel.setStart(container, offset);
            newSel.setEnd(container, offset+1);
            restoreSelection(newSel);
            document.execCommand('removeFormat', false, true);
          } else {
            document.execCommand('removeFormat', false, true);
          }
        }
      },

      link: {
        exec: function() {
          removeFormat();
          document.execCommand('createLink', false, window.prompt('URL:', 'http://'));
        }
      },
      image: {
          exec: function() {
            removeFormat();
            document.execCommand('insertImage', false, window.prompt('Image URL:', 'http://'));      
          }
        },
      ul: {
        isActive: function() {
          return document.queryCommandState('insertUnorderedList', false, true);
        },
        exec: function() {
          document.execCommand('insertUnorderedList', false, true);
        }
      },

      ol: {
        isActive: function() {
    	  return document.queryCommandState('insertOrderedList', false, true);
        },
        exec: function() {
          document.execCommand('insertOrderedList', false, true);
        }
      },

      indent: {
        exec: function() {
          if (document.queryCommandState('insertOrderedList', false, true) ||
              document.queryCommandState('insertUnorderedList', false, true)) {
            document.execCommand('indent', false, true);
          }
        }
      },

      outdent: {
        exec: function() {
          if (document.queryCommandState('insertOrderedList', false, true) ||
              document.queryCommandState('insertUnorderedList', false, true)) {
            document.execCommand('outdent', false, true);
          }
        }
      },
      justifyLeft: {
          isActive: function() {
              return document.queryCommandState('justifyLeft', false, true);
           },    	  
          exec: function() {
              removeFormat();
              document.execCommand('justifyLeft', false, true);
            }
      },
      justifyCenter: {
          isActive: function() {
              return document.queryCommandState('justifyCenter', false, true);
           },
          exec: function() {
              removeFormat();
              document.execCommand('justifyCenter', false, true);
            }
      },
      justifyRight: {
          isActive: function() {
              return document.queryCommandState('justifyRight', false, true);
           },    	  
          exec: function() {
              removeFormat();
              document.execCommand('justifyRight', false, true);
            }
      }
    };
    
    // Returns true if a and b is the same font family. This is used to check
    // if the current font family (`document.queryCommandValue('fontName')`)
    // is the font family that's used to style code.
    function cmpFontFamily(a, b) {
      function normalizeFontFamily(s) {
        return (''+s).replace(/\s*,\s*/g, ',').replace(/'/g, '"');
      }
      
      a = normalizeFontFamily(a);
      b = normalizeFontFamily(b);
      // Internet Explorer's `document.queryCommandValue('fontName')` returns
      // only the applied font family (e.g. `Consolas`), not the full font
      // stack (e.g. `Monaco, Consolas, "Lucida Console", monospace`).
      if ($.browser.msie) {
        if (a.split(',').length === 1) {
          return b.split(',').indexOf(a) > -1;
        } else if (b.split(',').length === 1) {
          return a.split(',').indexOf(b) > -1;
        } else {
          return a === b;
        }
      } else {
        return a === b;
      }
    }
    
    
    // Semantify/desemantify content
    // -----------------------------
    
    function escape(text) {
      return text.replace(/&/g, '&amp;')
                 .replace(/</g, '&lt;')
                 .replace(/>/g, '&gt;')
                 .replace(/"/g, '&quot;');
    }
    
    // Recursively walks the dom and returns the semantified contents. Replaces
    // presentational elements (e.g. `<b>`) with their semantic counterparts
    // (e.g. `<strong>`).
    function semantifyContents(node) {
      function replace(presentational, semantic) {
        node.find(presentational).each(function () {
          $(this).replaceWith($(document.createElement(semantic)).html($(this).html()));
        });
      }
      replace('i', 'em');
      replace('b', 'strong');
      replace('.proper-code', 'code');
      replace('div', 'p');
      //replace('span', 'span');
      
      node.find('span').each(function () {
        if (this.firstChild) {
          $(this.firstChild).unwrap();
        }
      });
      
      node.find('p, ul, ol').each(function () {
        while ($(this).parent().is('p')) {
          $(this).unwrap();
        }
      });
      
      // Fix nested lists
      node.find('ul > ul, ul > ol, ol > ul, ol > ol').each(function () {
        if ($(this).prev()) {
          $(this).prev().append(this);
        } else {
          $(this).wrap($('<li />'));
        }
      });
      
      (function () {
        var currentP = [];
        function wrapInP() {
          if (currentP.length) {
            var p = $('<p />').insertBefore(currentP[0]);
            for (var i = 0, l = currentP.length; i < l; i++) {
              $(currentP[i]).remove().appendTo(p);
            }
            currentP = [];
          }
        }
        // _.clone is necessary because it turns the `childNodes` live
        // dom collection into a static array.
        var children = _.clone(node.get(0).childNodes);
        for (var i = 0, l = children.length; i < l; i++) {
          var child = children[i];
          if (!$(child).is('p, ul, ol') &&
              !(child.nodeType === Node.TEXT_NODE && (/^\s*$/).exec(child.data))) {
            currentP.push(child);
          } else {
            wrapInP();
          }
        }
        wrapInP();
      })();
      
      // Remove unnecessary br's
      node.find('br').each(function () {
        if (this.parentNode.lastChild === this) {
          $(this).remove();
        }
      });
      
      // Remove all spans
      node.find('span').each(function () {
        $(this).children().first().unwrap();
      });
    }
    
    // Replaces semantic elements with their presentational counterparts
    // (e.g. <em> with <i>).
    function desemantifyContents(node) {
      doWithSelection(function () {
        function replace(semantic, presentational) {
          node.find(semantic).each(function () {
            var presentationalEl = $(presentational).get(0);
            
            var child;
            while (child = this.firstChild) {
              presentationalEl.appendChild(child);
            }
            
            $(this).replaceWith(presentationalEl);
          });
        }
        replace('em', '<i />');
        replace('strong', '<b />');
        replace('code', '<font class="proper-code" face="'+escape(options.codeFontFamily)+'" />');
      });
    }
    
    // Update the control buttons' state.
    function updateCommandState() {
      if (!options.markup) return;
      
      $controls.find('.btn').removeClass('active');
      _.each(commands, function(command, name) {
        if (command.isActive && command.isActive()) {
        	$controls.find('.btn.'+name).addClass('active');
        }
      });
    }
    
    
    // Placeholder
    // -----------
    
    // If the activeElement has no content, display the placeholder and give
    // the element the class `empty`.
    function maybeInsertPlaceholder() {
    	//console.log();
      if (($(activeElement).text().trim().length === 0) && ($(activeElement).find('img').length==0) ) {
        $(activeElement).addClass('empty');
        if (options.markup) {
          $(activeElement).html('&laquo; '+options.placeholder+' &raquo;');
        } else {
          $(activeElement).html('&laquo; '+options.placeholder+' &raquo;');
        }
      }
    }
    
    // If the activeElement has the class `empty`, remove the placeholder and
    // the class.
    function maybeRemovePlaceholder() {
      if ($(activeElement).hasClass('empty')) {
        $(activeElement).removeClass('empty');
        selectAll();
        //document.execCommand('delete', false, "");
      }
    }
    
    
    // DOM Selection
    // -------------
    
    // Returns the current selection as a dom range.
    function saveSelection() {
      if (window.getSelection) {
        var sel = window.getSelection();
        if (sel.rangeCount > 0) {
          return sel.getRangeAt(0);
        }
      } else if (document.selection && document.selection.createRange) { // IE
        return document.selection.createRange();
      }
      return null;
    }
    
    // Selects the given dom range.
    function restoreSelection(range) {
      if (range) {
        if (window.getSelection) {
          var sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
        } else if (document.selection && range.select) { // IE
          range.select();
        }
      }
    }
    
    // Selects the whole editing area.
    function selectAll() {
      var range = document.createRange();
      range.selectNodeContents($(activeElement)[0]);
      restoreSelection(range);
    }
    
    // Applies fn and tries to preserve the user's selection and cursor
    // position.
    function doWithSelection (fn) {
      // Before
      var sel = saveSelection()
      if (sel) {
        var startContainer = sel.startContainer
        ,   startOffset    = sel.startOffset
        ,   endContainer   = sel.endContainer
        ,   endOffset      = sel.endOffset;
      }
      
      fn();
      
      if (sel) {
        // After
        function isInDom(node) {
          if (node === document.body) return true;
          if (node.parentNode) return isInDom(node.parentNode);
          return false;
        }
        if (isInDom(startContainer)) {
          sel.setStart(startContainer, startOffset);
        }
        if (isInDom(endContainer)) {
          sel.setEnd(endContainer, endOffset);
        }
        restoreSelection(sel);
      }
    }
    
    
    // Handle events
    // -------------
    
    // Should be called during a paste event. Removes the focus from the
    // currently focused element. Expects a callback function that will be
    // called with a node containing the pasted content.
    function getPastedContent (callback) {
      // TODO: michael, explain why these css properties are needed -- timjb
      var tmpEl = $('<div id="proper_tmp_el" contenteditable="true" />')
        .css({ position: 'fixed', top: '20px', left: '20px', opacity: '0' })
        .appendTo(document.body)
        .focus();
      setTimeout(function () {
        tmpEl.remove();
        callback(tmpEl);
      }, 10);
    }
    
    function cleanPastedContent (node) {
      var allowedTags = {
        p: [], ul: [], ol: [], li: [],
        strong: [], code: [], em: [], b: [], i: [], a: ['href']
      };
      
      function traverse (node) {
        // Remove comments
        $(node).contents().filter(function () {
          return this.nodeType === Node.COMMENT_NODE
        }).remove();
        
        $(node).children().each(function () {
          var tag = this.tagName.toLowerCase();
          traverse(this);
          if (allowedTags[tag]) {
            var old  = $(this)
            ,   neww = $(document.createElement(tag));
            neww.html(old.html());
            _.each(allowedTags[tag], function (name) {
              neww.attr(name, old.attr(name));
            });
            old.replaceWith(neww);
          } else if (tag === 'font' && $(this).hasClass('proper-code')) {
            // do nothing
          } else {
            $(this).contents().first().unwrap();
          }
        });
      }
      
      $(node).find('script, style').remove();
      // Remove double annotations
      var annotations = 'strong, em, b, i, code, a';
      $(node).find(annotations).each(function () {
        $(this).find(annotations).each(function () {
          $(this).contents().first().unwrap();
        });
      });
      traverse(node);
    }
    
    // Removes <b>, <i> and <font> tags
    function removeAnnotations (node) {
      $(node).find('b, i, font').each(function () {
        $(this).contents().first().unwrap();
      });
    }
    
    function bindEvents(el) {
      $(el)
        .unbind('paste')
        .unbind('keydown')
        .unbind('keyup')
        .unbind('focus')
        .unbind('blur');
      
      $(el).bind('paste', function () {
        var isAnnotationActive = commands.strong.isActive()
                              || commands.em.isActive()
                              || commands.code.isActive();
        var selection = saveSelection();
        getPastedContent(function (node) {
          restoreSelection(selection);
          $(el).focus();
          cleanPastedContent($(node));
          //semantifyContents($(node));
          desemantifyContents($(node));
          if (isAnnotationActive) removeAnnotations($(node));
          // For some reason last </p> gets injected anyway
          document.execCommand('insertHTML', false, $(node).html());
        });
      });
      
      function isTag(node, tag) {
        if (!node || node === activeElement) return false;
        if (node.tagName && node.tagName.toLowerCase() === tag) return true;
        return isTag(node.parentNode, tag);
      }
      
      // Prevent multiline
      $(el).bind('keydown', function(e) {
        if (!options.multiline && e.keyCode === 13) {
          e.stopPropagation();
          e.preventDefault();
          return;
        }
        if (e.keyCode === 8 &&
            $(activeElement).text().trim() === '' &&
            $(activeElement).find('p, li').length === 1) {
          // backspace is pressed and the editor is empty
          // prevent the removal of the last paragraph
          e.preventDefault();
        }
        // By default, Firefox doesn't create paragraphs. Fix this.
        if ($.browser.mozilla) {
          var selectionStart = saveSelection().startContainer;
          //added other tags: pre,h5,h4,h3
          if (options.multiline && !isTag(selectionStart, 'p') && !isTag(selectionStart, 'ul') && !isTag(selectionStart, 'ol') && !isTag(selectionStart, 'pre')&& !isTag(selectionStart, 'h5')&& !isTag(selectionStart, 'h4')&& !isTag(selectionStart, 'h3')) {
            document.execCommand('insertParagraph', false, true);
          }
          if (e.keyCode === 13 && !e.shiftKey) {
            window.setTimeout(function () {
              if (!isTag(selectionStart, 'ul')) {
                document.execCommand('insertParagraph', false, true);
              }
            }, 10);
          }
        }
      });
      
      $(el)
        .bind('focus', maybeRemovePlaceholder)
        .bind('blur', maybeInsertPlaceholder)
        .bind('click', updateCommandState);
      
      $(el).bind('keyup', function(e) {        
        updateCommandState();
        addCodeClasses();
        // Trigger change events, but consolidate them to 200ms time slices
        setTimeout(function() {
          // Skip if there's already a change pending
          if (!pendingChange) {
            pendingChange = true;
            setTimeout(function() {
              pendingChange = false;
              events.trigger('changed');
            }, 200);
          }
        }, 10);
        return true;
      });
    }
    
    // Instance methods
    // -----------

    function deactivate () {
      $(activeElement)
        .attr('contenteditable', 'false')
        .unbind('paste')
        .unbind('keydown');
      $('.proper-commands').remove();
      events.unbind('changed');
    };
    
    function disable(){
    	$.each($('.proper-commands .btn'),function(){
    		if (!$(this).hasClass('save') && !$(this).hasClass('fullscreen')&& !$(this).hasClass('redo')&& !$(this).hasClass('undo')){
    			$(this).addClass('button_inactive');
    		}
    	});
	};
    function enable(){
    	$.each($('.button_inactive'),function(){
    		$(this).removeClass('button_inactive');
    		$(this).addClass('button_active');
    	});
	};	
    // Activate editor for a given element
    function activate (el, opts) {
      options = {};
      _.extend(options, defaultOptions, opts);
      
      // Deactivate previously active element
      deactivate();
      
      // Make editable
      $(el).attr('contenteditable', true);
      activeElement = el;
      bindEvents(el);
      
      // Setup controls
      if (options.markup) {
        $controls = $(controlsTpl); 
        $controls.appendTo($(options.controlsTarget));
      }
      
      // Keyboard bindings
      if (options.markup) {
        function execLater(cmd) {
          return function(e) {
            e.preventDefault();
            exec(cmd);
          };
        }
        $(activeElement)
          .keydown('ctrl+s', execLater('save'))
          .keydown('ctrl+shift+e', execLater('em'))
          .keydown('ctrl+shift+5', execLater('h5'))
          .keydown('ctrl+shift+4', execLater('h4'))
          .keydown('ctrl+shift+3', execLater('h3'))
          .keydown('ctrl+shift+s', execLater('strong'))
          .keydown('ctrl+shift+c', execLater('code'))
          .keydown('ctrl+shift+q', execLater('quote'))
          .keydown('ctrl+shift+l', execLater('link'))
          .keydown('ctrl+shift+i', execLater('image'))
          .keydown('ctrl+shift+b', execLater('ul'))
          .keydown('ctrl+shift+n', execLater('ol'))
          .keydown('tab',          execLater('indent'))
          .keydown('ctrl+shift+f', execLater('fullscreen'))
          .keydown('ctrl+shift+h', execLater('htmlCode'))
          .keydown('ctrl+shift+r', execLater('removeFormat'))
          .keydown('shift+tab',    execLater('outdent'));
      }
      
      $(activeElement).focus();
      updateCommandState();
      desemantifyContents($(activeElement));
      
      // Use <b>, <i> and <font face="monospace"> instead of style attributes.
      // This is convenient because these inline element can easily be replaced
      // by their more semantic counterparts (<strong>, <em> and <code>).
      document.execCommand('styleWithCSS', false, false);
      
      $('.proper-commands a').click(function(e) {
    	 if(!$(this).hasClass('button_inactive')){
	        e.preventDefault();
	        $(activeElement).focus();
	        exec($(e.currentTarget).attr('command'));
	        updateCommandState();
	        setTimeout(function() { events.trigger('changed'); }, 10);
    	 }
      });
  	//enable color picker
  	$('#color-picker').colorPicker();
    };
    
    // Get current content
    function content () {
      if ($(activeElement).hasClass('empty')) return '';
      
      if (options.markup) {
        if (!activeElement) return '';
        var clone = $(activeElement).clone();
        semantifyContents(clone);
        return clone.html();
      } else {
        if (options.multiline) {
          return _.stripTags($(activeElement).html().replace(/<div>/g, '\n')
                                             .replace(/<\/div>/g, '')).trim();
        } else {
          return _.stripTags($(activeElement).html()).trim();
        }
      }
    };
	
    // Expose public API
    // -----------------
    
    return {
      bind:    function () { events.bind.apply(events, arguments); },
      unbind:  function () { events.unbind.apply(events, arguments); },
      trigger: function () { events.trigger.apply(events, arguments); },
      
      activate: activate,
      deactivate: deactivate,
      disable: disable,
      enable: enable,
      content: content,
      exec: exec,
      commands: commands
    };	
  };
})();
