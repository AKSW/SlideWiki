// extract properties for the hash part of address
function getPropertiesFromHash(hash){
	var output=new Array();
	var parts=hash.split("-");
	output['deckId']=parts[1];
	output['type']=parts[2];
	output['itemId']=parts[3];
	output['pos']=parts[4];
	output['mode']=parts[5];
	if(hash)
		output['nodeId']='tree-'+parts[1]+'-'+parts[2]+'-'+parts[3]+'-'+parts[4];
	return output;
}
//updates address/title for each mode
function updateModeAddress(selected_id,mode,changeHash){
	var main=$("#deck_title_span").text().trim();
	var current=$('#'+selected_id).text().trim();
	var title='';
	if(main==current)
		title=main;
	else
		title=main+': '+current;
	//$.address.value(selected_id+'-'+mode);
	//$.address.title(selected_id+'-'+mode);
	//console.log(window.location);
	//window.location.hash=selected_id+'-'+mode;
	var qs=window.location.search;
	var tmp=qs.split("\/"); 
	//console.log(tmp[0]);
	if(tmp[0]=='?url=main'){
		//console.log(window.location);
		var n=qs.match(/\?url\=main\/deck\&deck\=([0-9]+)([a-z]*)/);
		//console.log(n[1]);
		window.location=window.location.pathname+'deck/'+n[1]+'_'+n[2];
	}else{
		if(changeHash)
			window.location=window.location.pathname+'#'+selected_id+'-'+mode;
	}
	
	//document.title=selected_id+'-'+mode;
	document.title=title;
}
//update tab urls according to current id
function updateTabURLs(selected_id, type){
	var tmp,absolute_pos=0;
	var selected_properties=getPropertiesFromId(selected_id);
	if (type=="slide"){
            $('#slideview').css('min-height','780px');
		$('#editlink').attr('onclick','show_deck_brand();editSlide('+selected_properties['deckId']+','+selected_properties['itemId']+','+selected_properties['pos']+');updateModeAddress("'+selected_id+'","edit",1)');
		$('#discussionlink').attr('onclick','hide_deck_brand();showDiscussion(\'slide\','+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","discussion",1)');
		$('#usagelink').attr('onclick','hide_deck_brand();showSlideUsage('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","use",1)');
		$('#questionslink').attr('onclick','hide_deck_brand();showSlideQuestions('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","quest",1)');
		$('#historylink').attr('onclick','hide_deck_brand();showSlideRevisions('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","history",1)');	
		if($.trim($("#"+selected_id+"-hasNewRevision").text())=="1")
			$('#historylink').html("History<img title='New revision is available!' src='static/img/exclamation.gif'>");
		else
			$('#historylink').html('History');
		//get absolute_pos for the selected slide
		if (!$("#tree").jstree("get_selected")[0]) {
			tmp=$('.jstree-clicked')[0]?$('.jstree-clicked')[0].title:'';
		} else {
			tmp=$("#tree").jstree("get_selected")[0].children[1].title;
		}
		if (!tmp){
			var hash=window.location.hash;
			var parameters=hash.split('#')[1];
			var parts=getPropertiesFromHash(parameters);	
			tmp=$('#'+parts['nodeId']).attr('title');
		}
		tmp=tmp.split('|')[2];
		absolute_pos=tmp.split(':')[1];
		$('#playSlide').attr('href','playSync/style/'+style+'/transition/'+transition+'/deck/'+deck+'#'+selected_id+'-'+(parseInt(absolute_pos)-1)+'-view');
		$('#shareLink').attr('onclick','showShareLink("slide","'+selected_properties['itemId']+'");');
		$("#downloadDeck").css("display",'none');
		$("#printDeck").css("display",'none');
		$("#current_slide_number").text(absolute_pos);
		//$("#total_slides_number").text();
	}else{
		$('#shareLink').attr('onclick','showShareLink("deck","'+selected_properties['itemId']+'");');
		$('#editlink').attr('onclick','hide_deck_brand();editDeck('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","edit",1)');
		$('#discussionlink').attr('onclick','hide_deck_brand();showDiscussion(\'deck\','+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","discussion",1)');
		$('#usagelink').attr('onclick','hide_deck_brand();showDeckUsage('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","use",1)');
		$('#questionslink').attr('onclick','hide_deck_brand();showDeckQuestions('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","quest",1)');
		$('#historylink').attr('onclick','hide_deck_brand();showDeckRevisions('+selected_properties['itemId']+');updateModeAddress("'+selected_id+'","history",1)');
		$('#historylink').html('History');
	}
	$('#viewlink').attr('onclick','show_deck_brand();selectNode("'+selected_id+'");updateModeAddress("'+selected_id+'","view",1)');	
}
function getCurrentHash(){
	//var hash=$.address.value();
	var hash=window.location.hash ;
	return hash;
}
function getNextSlideURL(){
	var hash=getCurrentHash();
	var properties=getPropertiesFromHash(hash);
	var next_id=getNextFromId(properties['nodeId']);
	return next_id;
}
function getPreviousSlideURL(){
	var hash=getCurrentHash();
	var properties=getPropertiesFromHash(hash);
	var previous_id=getPreviousFromId(properties['nodeId']);
	return previous_id;
}
function handleAddressChange(event){
/*
	var properties=getPropertiesFromHash(event.value);
	var id=properties['nodeId'];
*/
}
function handleInitialAddress(){
	var hash=window.location.hash;
	var parameters=hash.split('#')[1];
	if(parameters){
		var parts=getPropertiesFromHash(parameters);
		if(parts['nodeId']){
			switch(parts['mode']){
				case "view":
					selectNode(parts['nodeId']);
					if(parts['type']=="slide"){
						updateTabURLs(parts['nodeId'], "slide");
					}else{
						updateTabURLs(parts['nodeId'], "deck");
					}		
					show_deck_brand();
					break;
				case "edit":
					hide_save_toolbar();
					selectTab('edit');
					if(parts['type']=="slide"){
						editSlide(parts['deckId'],parts['itemId'],parts['pos']);
						updateTabURLs(parts['nodeId'], "slide");
					}else{
						editDeck(parts['itemId']);
						updateTabURLs(parts['nodeId'], "deck");
					}
					highlightNode(parts['nodeId']);
					break;						
				case "discussion":
					hide_save_toolbar();
					selectTab('discussion');
					showDiscussion(parts['type'],parts['itemId']);
					if(parts['type']=="slide"){
						updateTabURLs(parts['nodeId'], "slide");
					}else{
						updateTabURLs(parts['nodeId'], "deck");
					}	
					highlightNode(parts['nodeId']);
					break;
				case "use":
					hide_save_toolbar();
					selectTab('usage');
					if(parts['type']=="slide"){
						showSlideUsage(parts['itemId']);
						updateTabURLs(parts['nodeId'], "slide");
					}else{
						showDeckUsage(parts['itemId']);
						updateTabURLs(parts['nodeId'], "deck");
					}		
					highlightNode(parts['nodeId']);
					break;	
				case "history":
					hide_save_toolbar();
					selectTab('history' );
					if(parts['type']=="slide"){
						showSlideRevisions(parts['itemId']);
						updateTabURLs(parts['nodeId'], "slide");
					}else{
						showDeckRevisions(parts['itemId']);
						updateTabURLs(parts['nodeId'], "deck");
					}	
					highlightNode(parts['nodeId']);	
					break;
				case "quest":
					hide_save_toolbar();
					selectTab('questions');
					if(parts['type']=="slide"){
						showSlideQuestions(parts['itemId']);
						updateTabURLs(parts['nodeId'], "slide");
					}else{
						showDeckQuestions(parts['itemId']);
						updateTabURLs(parts['nodeId'], "deck");
					}	
					highlightNode(parts['nodeId']);
					break;
			}
		}
	}else{
		selectNode("tree-0-deck-"+deck+"-1");
		updateTabURLs("tree-0-deck-"+deck+"-1", "deck");
		show_deck_brand();
		createBreadCrumb("tree-0-deck-"+deck+"-1");
	}
}