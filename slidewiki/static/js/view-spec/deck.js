$.deck.defaults.keys.next = [];// up
$.deck.defaults.keys.previous = [];// down
$.deck.defaults.keys.menu = [];
$.deck.defaults.keys.scale = [];
$.deck.defaults.keys.goto = [];

var AMTcgiloc = "http://www.imathas.com/cgi-bin/mimetex.cgi";           //change it to local in future
var AScgiloc = 'libraries/frontend/tinymce/php/svgimg.php';
	
MathJax.Hub.Config({
	skipStartupTypeset: true,
	extensions: ["tex2jax.js"],
	jax: ["input/TeX", "output/HTML-CSS"],
	menuSettings: { context: "Browser" },
	tex2jax: {
		inlineMath: [ ['$','$'], ["\\(","\\)"] ],
		displayMath: [ ['$$','$$'], ["\\[","\\]"] ],
		processEscapes: true
	},
	"HTML-CSS": { availableFonts: ["TeX"] }
});

