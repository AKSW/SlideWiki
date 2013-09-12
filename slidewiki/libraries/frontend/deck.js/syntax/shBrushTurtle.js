/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/SyntaxHighlighter
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/SyntaxHighlighter/donate.html
 *
 * @version
 * 3.0.83 (July 02 2010)
 * 
 * @copyright
 * Copyright (C) 2004-2010 Alex Gorbatchev.
 *
 * @license
 * Dual licensed under the MIT and GPL licenses.
 */

(function() {
    typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

    function Brush() {
        var keywords = 'prefix a ';
        var r = SyntaxHighlighter.regexLib;

        this.regexList = [
            { regex: r.multiLineDoubleQuotedString, css: 'string' }, // double quoted strings
            { regex: r.multiLineSingleQuotedString, css: 'string' }, // single quoted strings
            { regex: r.singleLineCComments, css: 'comments' }, // one line comments
            { regex: r.multiLineCComments, css: 'comments' }, // multiline comments
            { regex: /(&lt;|<)[\s\S]*?(&gt;|>)/gm, css: 'comments' }, // URI
            { regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'keyword' } // keywords
        ];

        this.forHtmlScript(r.scriptScriptTags);
    }

    Brush.prototype = new SyntaxHighlighter.Highlighter();
    Brush.aliases = ['turtle', 'n3'];

    SyntaxHighlighter.brushes.JScript = Brush;

    // CommonJS
    typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();

