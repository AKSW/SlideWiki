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
});
function runEffects(selected_id){
	var options = {};
	var exec_time=450;
	$.each($('#'+selected_id+' .effect-shake'),function(index,value){
		$(value).effect( 'shake', options, exec_time, effectscallback );
	});	
	$.each($('#'+selected_id+' .effect-bounce'),function(index,value){
		$(value).effect( 'bounce', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-explode'),function(index,value){
		$(value).effect( 'explode', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-highlight'),function(index,value){
		$(value).effect( 'highlight', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-scale'),function(index,value){
		options = { percent: 0 };
		$(value).effect( 'scale', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-transfer'),function(index,value){
		options = { to: "#button", className: "ui-effects-transfer" };
		$(value).effect( 'transfer', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-slide'),function(index,value){
		$(value).effect( 'slide', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-size'),function(index,value){
		options = { to: { width: 200, height: 60 } };
		$(value).effect( 'size', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-pulsate'),function(index,value){
		$(value).effect( 'pulsate', options, exec_time, effectscallback );
	});	
	$.each($('#'+selected_id+' .effect-puff'),function(index,value){
		$(value).effect( 'puff', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-fold'),function(index,value){
		$(value).effect( 'fold', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-fade'),function(index,value){
		$(value).effect( 'fade', options, exec_time, effectscallback );
	});	
	$.each($('#'+selected_id+' .effect-drop'),function(index,value){
		$(value).effect( 'drop', options, exec_time, effectscallback );
	});
	$.each($('#'+selected_id+' .effect-clip'),function(index,value){
		$(value).effect( 'clip', options, exec_time, effectscallback );
	});	
	$.each($('#'+selected_id+' .effect-blind'),function(index,value){
		$(value).effect( 'blind', options, exec_time, effectscallback );
	});		
}
function effectscallback() {

}
function checkLoadStatus(selected_id){
	var selected_index,overal_index;
	//handle progressive load here when we are reaching the outside of predefined range
	selected_index=loaded_range.indexOf(selected_id);
	overal_index=all_slides.indexOf(selected_id);
	//run predefined annimation effects
	runEffects(selected_id);
	//correct slide numbers
	$('.deck-status-current').text(overal_index+1);
	//do not load when load_range is 0
	if(((selected_index==loaded_range.length-1) && loaded_range.length)|| (selected_index==0)){
		//prevent loop for first and last slides of root deck
		if((overal_index!=0)&& (overal_index!=all_slides.length-1)){
			loaded_range=[];
			progressiveLoadSlide(selected_id);
		}
	}	
}
//converts inslide class to slide class
function enableInslides(){
	$.each($('.inslide'),function(index,value){
		$(value).addClass('slide');
		$(value).removeClass('inslide');
	});	
}
function progressiveLoadSlide(selected_id) {
	var range_positions=new Array();
	var oldqr='';
	if(($('#qrcode').length)  && master==true){
		oldqr=$("#qrcodediv")[0].outerHTML;
	}	
	if ($("#slide-area").html() != '') {
		$("#slide-area").html('');
	}
	if (loaded_range.indexOf(selected_id)==-1) {
		range_positions=fillRangeOfSlides(selected_id);
		$.ajax({
					url : './?url=ajax/getSlidesByRange&id=' + deck +'&from='+range_positions[0]+'&to='+range_positions[1],
					success : function(msg) {
						var data = eval("(" + msg + ")");
						$("#slide_preview").tmpl(data).appendTo("#slide-area");
						if(oldqr!=''){
							$("#slide-area").append(oldqr);
							$('#qrcode').html('');
							$('#qrcode').qrcode($('#qrlink').attr('href').trim());
							$("#qrcodediv").draggable({ cancel: "span" });
						}
						//$("#slideview").append('<div class="slide-footer">'+data.footer_text+'<div class="deck-status"><span class="deck-status-current"></span>/<span class="deck-status-total"></span></div></div><p class="deck-status"><span class="deck-status-current"></span>/<span class="deck-status-total"></span></p><a href="#" class="deck-prev-link" title="Previous">&#8592;</a><a href="#" class="deck-next-link" title="Next">&#8594;</a>');
						//$("#slide-area").append('<div class="deck-status"><span id="current_slide_number" class="deck-status-current"></span>/<span id="total_slides_number">'+all_slides.length+'</span></div><a href="#" class="deck-prev-link" title="Previous">&#8592;</a><a href="#" class="deck-next-link" title="Next">&#8594;</a>');				
						enableInslides();
						if(sid==0 && master==false){
							sid = new Date().getTime();
							master = true;
						}		
						console.log(sid);
						$.deck('.slide');
						$.deck('iframes');
						$.deck('remote', {
						    // pass server & port, those are the same for all
						    server: 'http://slidewiki.org',
						    port: 5000,

						    // vars defined in step 2
						    sessionId: sid,
						    master: master
						});							
						$('.deck-status-total').text(all_slides.length);;
						//if(scaling)
							//$.deck('enableScale');
						//else
							//$.deck('disableScale');
						MathJax.Hub.Queue(["Typeset",MathJax.Hub,'slide-area']);
						//apply_fullscreen_slide();
						apply_play_fullscreen();
						$("#ajax_progress_indicator").html('');
						$("#ajax_progress_indicator").remove();
						//add qr code
						if(!($('#qrcode').length) && master==true && oldqr==''){
							$('#slide-area').append('<div id="qrcodediv"> <p class="qr-close-btn"> <a style="cursor:pointer;font-weight:bold;" onclick="hideQR();" title="Use \'Q\' shortcut to toggle the QR code">x</a>&nbsp;</p><p class="ui-widget-header">Follow this presentation on your device:<br/><a href="" id="qrlink" target="_blank"><span id="qrlinktext"></span></a> </p><div id="qrcode"></div></div>');
							var link="http://slidewiki.org/playSync/sid/"+sid+"/style/"+style+"/transition/"+transition+"/deck/"+deck;
							$.ajax({
								url : './?url=ajax/getShortPlaySyncURL&q='+link,
								success : function(msg) {
									$('#qrcode').qrcode(msg);
									$('#qrlink').attr('href',msg);
									$('#qrlinktext').html(' '+msg+' ');
									$("#qrcodediv").draggable({ cancel: "span" });
								}
							});	
						}
					}
		});
	}        
}
function hideQR(){
	$("#qrcodediv").hide();
}
function getSlideStatusForProgressive(selected_id){
	return all_slides.indexOf(selected_id)+1;
}
function fillRangeOfSlides(selected_id){
	var start,end;
	var i=0;
	var output=new Array();
	//range window size -> n*2 +1
	var tmp,top_add,bottom_add,total,n=35;
	if (detectmob())
		n=6;
	var selected_index=all_slides.indexOf(selected_id);
	total=(all_slides.length)-1;
	//console.log(total);
	tmp=selected_index-n;
	if(tmp<0){
		start=0;
		bottom_add=n-selected_index; //to balance the range
	}else{
		start=tmp;
		bottom_add=0;
	}
	if((selected_index+n)>total){
		end=total;
		top_add=n-(total-selected_index);
		for (i=1;i<=top_add;i++)
		{
			if((start-1)>=0){
				start=start-1;
			}
		}
	}else{
		top_add=0;
		end=selected_index+n;
		for (i=1;i<=bottom_add;i++)
		{
			if((end+1)<total){
				end=end+1;
			}
		}
	}
	for (i=start;i<=end;i++)
	{
		loaded_range.push(all_slides[i]);
	}
	return Array(start, end);
}
function removeBRs(input){
	var r=input.replace(/<br>/gi,''); 
	r=r.replace(/<br[0-9a-zA-Z]+>/gi,'');
	//r = r.replace(/\r\n\r\n/g, '');
	return r;
}
//Unescape HTML entities
function htmlDecode(input){
	  var e = document.createElement('div');
	  e.innerHTML = input;
	  return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}
//detect if mobile device is used
function detectmob() { 
	 if( navigator.userAgent.match(/Android/i)
	 || navigator.userAgent.match(/webOS/i)
	 || navigator.userAgent.match(/iPhone/i)
	 || navigator.userAgent.match(/iPad/i)
	 || navigator.userAgent.match(/iPod/i)
	 || navigator.userAgent.match(/BlackBerry/i)
	 || navigator.userAgent.match(/Windows Phone/i)
	 ){
	    return true;
	  }
	 else {
	    return false;
	  }
}