$(function(){
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
	
	$.deck('.slide');
	if(scaling)
		$.deck('enableScale');
	else
		$.deck('disableScale');
	MathJax.Hub.Queue(["Typeset",MathJax.Hub,'slide-area']);
	//apply_fullscreen_slide();
	apply_play_fullscreen();
});


