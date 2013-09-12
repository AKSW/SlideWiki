$.deck.defaults.keys.next = [];//up
$.deck.defaults.keys.previous = [];//down
$.deck.defaults.keys.menu = [];
$.deck.defaults.keys.scale = [];
$.deck.defaults.keys.goto = []; 

MathJax.Hub.Config({
	skipStartupTypeset: true,
    extensions: ["tex2jax.js"],
    jax: ["input/TeX", "output/HTML-CSS"],
    tex2jax: {
      inlineMath: [ ['$','$'], ["\\(","\\)"] ],
      displayMath: [ ['$$','$$'], ["\\[","\\]"] ],
      processEscapes: true
    },
    "HTML-CSS": { availableFonts: ["TeX"] }
  });
  
function compileSCSS(){
	var varfuncs=$('#scss_varfunc').val().trim();
	varfuncs=varfuncs.replace(/[a-zA-Z]+\s*\{((.|\n)*)\}/gi, "");
	$('#scss_varfunc').val(varfuncs);
	var input_data=varfuncs+' .deck-container { '+$('#scss_input').val().trim()+'}';
	if(input_data){
		$.ajax({
			type : "POST",
			url : "./?url=ajax/compileSCSS",
			data : 'input='+encodeURIComponent(input_data),
			//contentType: "application/x-www-form-urlencoded",
			success : function(msg) {
				$('#css_input').val(msg);
				$('#slidestyle').text(msg)
			}
			//,error: function(xhr, txt, err){ alert("Error!"); },
		});
	}
}