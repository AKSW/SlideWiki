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
	
	MathJax.Hub.Queue(["Typeset",MathJax.Hub,'slide-area']);
});
