$(function(){
   
	$.deck.defaults.keys.next = [];
	$.deck.defaults.keys.previous = [];
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
		"HTML-CSS": {availableFonts: ["TeX"]}
	});
	$.ajax({
		url : './?url=ajax/getNewDecks',
		beforeSend: function() {
			$("#ajax_progress_indicator").css('display', 'none');
				$("#new").html('<img src="./static/img/ajax_loader.gif"  align="center">');
			  },
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$("#new").html('');
			$.each(data.decks, function(i,v){
				if(v.abstract)
					v.abstract=v.abstract.trunc(180,true);
			});			
			$("#deck_preview").tmpl(data).appendTo("#new");	
			runDeckJS();
                        
                        
              
		}
	});		

	$.ajax({
		url : './?url=ajax/getFeaturedDecks',
		beforeSend: function() {
		$("#ajax_progress_indicator").css('display', 'none');
			$("#featured").html('<img src="./static/img/ajax_loader.gif"  align="center">');
		  },		
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$("#featured").html('');
			$.each(data.decks, function(i,v){
				if(v.abstract)
					v.abstract=v.abstract.trunc(180,true);
			});
			$("#deck_preview").tmpl(data).appendTo("#featured");	
			runDeckJS();
		}
	});
	$.ajax({
		url : './?url=ajax/getStats',
		beforeSend: function() {
		$("#ajax_progress_indicator").css('display', 'none');
			$("#home_statistics").html('<img src="./static/img/ajax_loader.gif"  align="center">');
		  },		
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$("#home_statistics").html('');
			data.number_of_decks=kFormatter(data.number_of_decks);
			data.number_of_deck_revisions=kFormatter(data.number_of_deck_revisions);
			data.number_of_slides=kFormatter(data.number_of_slides);
			data.number_of_slide_revisions=kFormatter(data.number_of_slide_revisions);
			data.number_of_questions=kFormatter(data.number_of_questions);
			data.number_of_users=data.number_of_users;
			$("#slidewiki_stats").tmpl(data).appendTo("#home_statistics");	
		}
	});
        
        $.ajax({
            url : './?url=ajax/getStream',
            beforeSend: function() {
		$("#ajax_progress_indicator").css('display', 'none');
			$("#home_activity_stream").html('<img src="./static/img/ajax_loader.gif"  align="center">');
		  },
              success: function(msg){                  
                  var data = eval('(' + msg + ')');                  
                  $('#home_activity_stream').empty();
                  for (var i in data.activities){                      
                      if (data.activities[i].type){
                          data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                          switch(data.activities[i].type){
                              case 'created_deck' :
                                  data.activities[i].type_text = 'created deck';
                                  break;
                              case 'translated_deck_from' :
                                  data.activities[i].type_text = 'translated deck';
                                  break;
                              case 'commented_deck_revision' :
                                  data.activities[i].type_text = 'commented deck';
                                  break;
                              case 'followed_deck' :
                                  data.activities[i].type_text = 'started following deck';
                                  break;
                              case 'translated_deck' :
                                  data.activities[i].type_text = 'translated deck';
                                  break;
                              case 'created_deck_revision' :
                                  data.activities[i].type_text = 'created deck revision';
                                  break;
                          }                          
                         $('#activity_only_text').tmpl(data.activities[i]).appendTo($('#home_activity_stream'));
                      }
                  }
              }   
                  
        })
        
        
        
	/*
	$.ajax({
		url : './?url=ajax/getDeckList&type=popular',
		beforeSend: function() {
			$("#ajax_progress_indicator").css('display', 'none');
				$("#popular").html('<img src="./static/img/ajax_loader.gif"  align="center">');
			  },
		success : function(msg) {
			var data = eval("(" + msg + ")");
			$("#popular").html('');
			$("#deck_preview").tmpl(data).appendTo("#popular");	
			runDeckJS();
		}
	});	
    */
});

function goToSlide(url){
    window.location = url;
    
}
//shorten large numbers in K
function kFormatter(num) {
    return num > 999 ? (num/1000).toFixed(1) + 'K' : num
}
function runDeckJS(){
	$.deck('.slide');
	MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'popular' ]);	
	MathJax.Hub.Queue([ "Typeset", MathJax.Hub, 'new' ]);	
	$.deck('showMenu');
	$.deck('iframes');
	overlay();         
        
}
String.prototype.trunc =
    function(n,useWordBoundary){
        var toLong = this.length>n,
            s_ = toLong ? this.substr(0,n-1) : this;
        s_ = useWordBoundary && toLong ? s_.substr(0,s_.lastIndexOf(' ')) : s_;
        return  toLong ? s_ + '&hellip;' : s_;
     };