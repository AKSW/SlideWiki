<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Slide <?php echo $slide_id;?> diff with previous revision</title>
	<script type="text/javascript" src="libraries/frontend/jquery.min.js"></script>
    <link rel="stylesheet" href="libraries/frontend/codemirror/lib/codemirror.css">
    <script src="libraries/frontend/codemirror/lib/codemirror.js"></script>
    <script src="libraries/frontend/codemirror/mode/javascript/javascript.js"></script>
    <script src="libraries/frontend/codemirror/mode/xml/xml.js"></script>
    <script src="libraries/frontend/codemirror/lib/util/formatting.js"></script>

    <style type="text/css">
      .CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black;}
    </style>
<script type="text/javascript" src="libraries/frontend/jsdifflib/dojo.xd.js"></script>
<script type="text/javascript" src="libraries/frontend/jsdifflib/diffview.js"></script>
<link rel="stylesheet" type="text/css" href="libraries/frontend/jsdifflib/diffview.css"/>
<script type="text/javascript" src="libraries/frontend/jsdifflib/difflib.js"></script>

<script language="javascript">
var $ = dojo.byId;
var url = window.location.toString().split("#")[0];

function compare_slides () {
	var base = difflib.stringAsLines(editor2.getValue());
	var newtxt = difflib.stringAsLines(editor1.getValue());
	var sm = new difflib.SequenceMatcher(base, newtxt);
	var opcodes = sm.get_opcodes();
	var diffoutputdiv = $("diffoutput");
	while (diffoutputdiv.firstChild) diffoutputdiv.removeChild(diffoutputdiv.firstChild);
	//var contextSize = $("contextSize").value;
	contextSize =  null;
	diffoutputdiv.appendChild(diffview.buildView({ baseTextLines:base,
												   newTextLines:newtxt,
												   opcodes:opcodes,
												   baseTextName:"Old Slide",
												   newTextName:"New Slide",
												   contextSize:contextSize,
												   viewType: $("inline").checked ? 1 : 0 }));
	window.location = url + "#diff";
}
</script>    
</head>
<body>
<table>
<tr id="diff_codemirror">
<td>
<div style="max-width: 50em;"><b>Old Slide:</b><br><textarea id="old-code-html" name="old-code-html"><?php echo $old_content;?></textarea></div>
</td>
<td>
<div style="max-width: 50em;"><b>New Slide:</b><br><textarea id="new-code-html" name="new-code-html"><?php echo $new_content;?></textarea></div>
</td>
</tr>
<tr>
<td colspan="2" align="center"> <button onclick="compare_slides()">Auto Compare</button> 
	<input type="radio" name="_viewtype" id="sidebyside"/> Side by Side
	&#160;&#160;
	<input type="radio" name="_viewtype" id="inline" checked="checked"/> Inline</td>
</tr>
<tr>
<td colspan="2">
	<a name="diff"> </a>
	<div id="diffoutput" style="width:100%"> </div>
</td>
</tr>
</table>

    <script id="script">
window.onload = function() {
  var te_html = document.getElementById("new-code-html");
  window.editor1 = CodeMirror.fromTextArea(te_html, {
    mode: "text/html",
    lineNumbers: true,
    lineWrapping: true,
	readOnly: true,
  })
  var totalLines = editor1.lineCount(); 
  var totalChars = editor1.getValue().length;
  var firstLineNumber = editor1.getOption('firstLineNumber');
  editor1.autoFormatRange({line:0, ch:0}, {line:totalLines, ch:totalChars});
  //CodeMirror.commands["selectAll"](editor1);
  //var range = getSelectedRange(editor1);
  //editor1.autoFormatRange(range.from, range.to);
  
  var te_html2 = document.getElementById("old-code-html");
  window.editor2 = CodeMirror.fromTextArea(te_html2, {
    mode: "text/html",
    lineNumbers: true,
    lineWrapping: true,
	readOnly: true,
  })
  var totalLines = editor2.lineCount(); 
  var totalChars = editor2.getValue().length;
  var firstLineNumber = editor2.getOption('firstLineNumber');
  editor2.autoFormatRange({line:0, ch:0}, {line:totalLines, ch:totalChars});
  //CodeMirror.commands["selectAll"](editor2);
  //var range = getSelectedRange(editor2);
  //editor2.autoFormatRange(range.from, range.to);  
  compare_slides();
  document.getElementById('diff_codemirror').style.display='none';
  
};

function getSelectedRange(editor) {
    return { from: editor.getCursor(true), to: editor.getCursor(false) };
  }
</script>
</body>
</html>
