	var followers_cache = null;
        var contributed_slides_cache = null;
        var contributed_decks_cache = null;
        var activity_stream_cache = new Array();
        
        
        function applyFilter() {
		checkbox = getCheckboxes();
		password = getPassword();
		$.ajax({
			async : false,
			url : 'ajax/currentUser',
			success : function(msg) {
				user = eval(msg);
				$.ajax({
					async : false,
					url : "./?url=ajax/updateFeedShowPreferences&user_id=" + user + "&show_contributed_slides=" + checkbox.contributed_slides + "&show_contributed_decks="+ checkbox.contributed_decks+ "&show_subscribed_slides="+ checkbox.subscribed_slides+"&show_subscribed_decks="+checkbox.subscribed_decks+"&show_subscribed_users="+checkbox.subscribed_users+"&page="+page,
					success : function(feed) {
						new_feed = eval('(' + feed + ')');
						subscribed_users = new_feed.pop();
						subscribed_slides = new_feed.pop();
						subscribed_decks = new_feed.pop();
						contributed_slides = new_feed.pop();
						contributed_decks = new_feed.pop();
						view_feed['items'] = getFeed(checkbox, contributed_slides, contributed_decks, subscribed_decks, subscribed_slides, subscribed_users);
						wiew_feed['tab'] = 'all';
                                                view(view_feed);
					}
				});
			}
		});
	}
	
	function getPassword() {
		password = $("#password").val();
		return password;
	}
	
	function getCheckboxes() {
		// choose all checkboxes
		checkboxes = $("input:checkbox");
		// get them into variables
		checkbox = new Array();
		for(var i = 0; i < checkboxes.length; i++) {
			checkbox[checkboxes[i].value] = checkboxes[i].checked;
		}
		return checkbox;
	}
	
        function emptyFeedArea(tab) {
            
            if (tab=='all'){
               $(".feed .contributed .slide").empty();
               $(".feed .contributed .deck").empty();
               $(".feed .subscribed .slide").empty();
               $(".feed .subscribed .deck").empty();
               $(".feed .subscribed .user").empty();
            }else {
                var tab_array = tab.split('_');            
                $(".feed ." + tab_array[0] +" ." + tab_array[1]).empty();
                
            }
            $('#contributed_slide').append('<div class="first"></div><div class="second"></div><div class="third"></div>');
            $('#contributed_deck').append('<div class="first"></div><div class="second"></div><div class="third"></div>');
            $('#subscribed_slide').append('<div class="first"></div><div class="second"></div><div class="third"></div>');
            $('#subscribed_deck').append('<div class="first"></div><div class="second"></div><div class="third"></div>');
            $('#subscribed_user').append('<div class="first"></div><div class="second"></div><div class="third"></div>');
            
                      
        }
	function view(feed) {
		// empty feed
		emptyFeedArea(feed.tab);
                var node = '' ;
                var page = '';
                var max = '';
		for(var i = 0; i < feed.items.length; i++) {
			feed_item = feed.items[i];
                        node = $('#'+feed_item.type);
                                if (node.find($('.first li')).size() > node.find($('.second li')).size()){
                                    node.find($('div.second')).append(feed_item.description);
                                }else {
                                    if (node.find($('.second li')).size() > node.find($('.third li')).size()){
                                    node.find($('div.third')).append(feed_item.description );
                                    } else {
                                        node.find($('div.first')).append(feed_item.description);
                                    }                                    
                                } 
			
		}                
                
                var pager = '<div class="pager" id="">' +
			'<a class="btn small success" name="prev_button" style="display:none;" onclick="goToPreviousPage()">Previous page</a>' +
			'<a class="btn small success" name="next_button" style="display:none;" onclick="goToNextPage()">Next page</a>' +
		'</div>';
                
            
                if (feed.tab=='all'){
                    $("#subscribed_slide").append(pager);
                    page = $("#subscribed_slide").attr('page');
                    max = $("#subscribed_slide").attr('max');
                    if(max > page - 0 + 1) {
                        $("#subscribed_slide").find('[name="next_button"]').show();
                    }               
                    
                    $("#subscribed_deck").append(pager);
                    page = $("#subscribed_deck").attr('page');
                    max = $("#subscribed_deck").attr('max');
                    if(max > page - 0 + 1) {
                        $("#subscribed_deck").find('[name="next_button"]').show();
                    }
                    
                    $("#contributed_slide").append(pager);
                    page = $("#contributed_slide").attr('page');
                    max = $("#contributed_slide").attr('max');
                    if(max > page - 0 + 1) {
                        $("#contributed_slide").find('[name="next_button"]').show();
                    }
                    
                    $("#contributed_deck").append(pager);
                    page = $("#contributed_deck").attr('page');
                    max = $("#contributed_deck").attr('max');
                    if(max > page - 0 + 1) {
                        $("#contributed_deck").find('[name="next_button"]').show();                        
                    }
                    
                    $("#subscribed_user").append(pager);
                    page = $("#subscribed_user").attr('page');
                    max = $("#subscribed_user").attr('max');
                    if(max > page - 0 + 1) {
                        $("#subscribed_user").find('[name="next_button"]').show();
                    }
                }else {
                    $("#" + feed.tab).append(pager);
                    page = $("#"+feed.tab).attr('page');
                    max = $("#"+feed.tab).attr('max');
                    if(max > page - 0 + 1) {
                        $("#"+feed.tab).find('[name="next_button"]').show();
                    }
                    if(page + 0 > 0){
                        $("#"+feed.tab).find('[name="prev_button"]').show();
                    }
                     
                }
        }
        function goToNextPage() {
		var tab = $('#main_feed').attr('tab');
                var page = $("#"+tab).attr('page');
                var max = $("#"+tab).attr('max');
		page++;
                $("#"+tab).attr('page',page);
                loadPage(tab,page);

	}
        function changeTab(link){           
            var new_link = link.split('#');
            $('#main_feed').attr('tab',new_link[1]);
        }
	
	function goToPreviousPage() {
                var tab = $('#main_feed').attr('tab');
		var page = $("#"+tab).attr('page');
                page--;
                $("#"+tab).attr('page',page);
                loadPage(tab,page);                        
//		
	}
    function loadPage(tab,page) {
                $.ajax({
                    async : false,
                    url : "./?url=ajax/loadUserPage&user_id=" + current_user + "&tab=" + tab + "&page="+page,
                    success : function(msg) {
                        feed = eval('(' + msg + ')');
                        view_feed = new Array();
                        view_feed['items'] = getPartOfFeed(tab, feed);
                        view_feed['tab'] = tab;
                        view(view_feed);
                    }
                });
    }
    function getPartOfFeed(tab_name, array_to_push){
        var feed = new Array();
        switch (tab_name) {
            case 'contributed_slide' :
                for(var i = 0; i < array_to_push.length; i++) {
                    slide = array_to_push[i];
                    element = makeElementFromSlide(current_user, slide, "contributed_slide")
                    feed.push(element);
                }
                break;
            case 'contributed_deck' :
                for(var i = 0; i < array_to_push.length; i++) {
                    deck = array_to_push[i];
                    element = makeElementFromDeck(current_user, deck, "contributed_deck");
                    feed.push(element);
                }
                break;
            case 'subscribed_deck' :
                for(var i = 0; i < array_to_push.length; i++) {
                    deck = array_to_push[i];
                    element = makeElementFromDeck(current_user, deck, "subscribed_deck");
                    feed.push(element);
                }
                break;
            case 'subscribed_slide' :
                for(var i = 0; i < array_to_push.length; i++) {
                    slide = array_to_push[i];
                    element = makeElementFromSlide(current_user, slide, "subscribed_slide");
                    feed.push(element);
                }
                break;
            case 'subscribed_user' :
                for(var i = 0; i < array_to_push.length; i++) {
                    user = array_to_push[i];
                    element = makeElementFromUser(current_user, user, "subscribed_user");
                    feed.push(element);
                }
                break;
        }
        return feed;
    }
    function getFeed(contributed_slides, contributed_decks, subscribed_decks, subscribed_slides, subscribed_users) {
        var feed = new Array();
        var deck = '';
        var slide = '';
        var element = '';
        var user = '';
        var i = 0;
	for(i = 0; i < contributed_slides.length; i++) {
            slide = contributed_slides[i];
            element = makeElementFromSlide(current_user, slide, "contributed_slide")
            feed.push(element);
        }
        for(i = 0; i < contributed_decks.length; i++) {
            deck = contributed_decks[i];            
            element = makeElementFromDeck(current_user, deck, "contributed_deck");
            feed.push(element);
        }
	// Subscribed items	
	for(i = 0; i < subscribed_decks. length; i++) {
            deck = subscribed_decks[i];
            element = makeElementFromDeck(current_user, deck, "subscribed_deck");
            feed.push(element);
        }
	for(i = 0; i < subscribed_slides.length; i++) {
            slide = subscribed_slides[i];
            element = makeElementFromSlide(current_user, slide, "subscribed_slide");
            feed.push(element);
	}
	for(i = 0; i < subscribed_users.length; i++) {
            user = subscribed_users[i];
            element = makeElementFromUser(current_user, user, "subscribed_user");
            feed.push(element);
	}
		
	//sort all feeds by timestamp
		
	return feed;
	}
	
	function makeElementFromSlide(current_user, slide, type) {
		element = new Array();
		// slide parameters
                description = new Array();
		description = getSlideDescription(slide);
		
		if(type === "contributed_slide") {
			timestamp = getSlideTimestamp(slide);
		} else if(type === "subscribed_slide") {
			timestamp = getFollowDate(current_user, slide);
		}
		
		element["description"] = description;
		element["timestamp"] = timestamp;
		element["type"] = type;
		return element;
	}
	
	function getSlideDescription(slide) {
		deck_num = slide.usage[0].id;
		tree_num = deck_num;
		at_deck_title = slide.usage[0].title;
		item_type = slide.item_name; // "slide"
		slide_num = slide.id; // revision id
		slide_pos = slide.position;
		
		if(slide.usage == "Not used!") {
			slide_tag = "<li><a href=\"";
			slide_tag += "slide/" + slide_num;
			slide_tag += "\">";
			slide_tag += slide.title;
			slide_tag += "</a></li>";
			
			at_tag = "";
		} else {		
			// slide <a href=""/> tag construction
			slide_tag = "<li><a href=\"";
                        //TODO: slug_title
			slide_tag += "deck/" + deck_num +
				   "#tree-"+ tree_num +
				   "-"+ item_type +
				   "-"+ slide_num +
				   "-"+ slide_pos +"-view";
			slide_tag += "\">";
			slide_tag += slide.title;
			slide_tag += "</a></li>";
			
			// the deck where this slide used
			at_tag = "<li><a href=\"";
			at_tag += "./";
                        //TODO: slug_title
			at_tag += "deck/" + deck_num;
			at_tag += "\">";
			at_tag += at_deck_title;
			at_tag += "</a></li>";
		}
                description = slide_tag;
//		description = new Array();
//		description['slide_tag'] = slide_tag;
//		if(at_tag != "") {
//			description ['deck_tag'] =  at_tag;
//		}
		
		return description;		
	}
	
	function getSlideTimestamp(slide) {
		return slide.revisionTime;
	}
	
	function makeElementFromDeck(current_user, deck, type) {
		var element = new Array();
                
		// deck parameters
		var description = getDeckDescription(deck);
		var timestamp = '';
		if(type === "contributed_deck") {
			timestamp = getDeckTimestamp(deck);
		} else if(type === "subscribed_deck") {
			timestamp = getFollowDate(current_user, deck);
		}		
		
		element["description"] = description;
		element["timestamp"] = timestamp;
		element["type"] = type;
		
		return element;
	}
	
	function getDeckDescription(deck) {
		var deck_num = deck.id;
		var deck_title = deck.title;                
		var a_tag = "<li><a href=\"";
                //TODO: slug_title
		a_tag += "deck/"+ deck_num;
		a_tag += "\">";
		a_tag += deck_title;
		a_tag += "</a></li>";
		
		var description = a_tag;
	
		return description;
	}
	
	function getDeckTimestamp(deck) {
		return deck.revisionTime;
	}
	
	function makeElementFromUser(current_user, user, type) {
		element = new Array();
		// user parameters
		description = getUserDescription(user);
		timestamp = getFollowDate(current_user, user);
		
		element["description"] = description;
		element["timestamp"] = timestamp;
		element["type"] = type;
		
		return element;
	}
	
	function getUserDescription(user) {
		id = user.id;
		username = user.username;
		
		a_tag = "<li><a href=\"";
		a_tag += "user/"+ id;
		a_tag += "\">";
		a_tag += username;
		a_tag += "</a></li>";
		
		description = a_tag;
		
		return description;
	}
	
	function getFollowDate(current_user, object) { // object can be deck, slide or user
		subscriptions = object.subscriptions;
                
		user_id = current_user;
		timestamp = "";
		// from slide subscriptions array
		
		for(var i = 0; i < subscriptions.length; i++) {
			subscription = subscriptions[i];
			if(subscription["user_id"] == user_id) {
				timestamp = subscription["timestamp"];
                                
			}
		}
                
		return timestamp;
	}
	
	function setNotification(notification_interval) {
		switch(notification_interval) {
			case "off":
				$("#interval").val("off");
				break;
			case "hourly":
				$("#interval").val("hourly");
				break;
			case "daily":
				$("#interval").val("daily");
				break;
			case "weekly":
				$("#interval").val("weekly");
				break;	
			default:
				break;
		}
	}
        
        function setDefaultTheme(default_theme) {
		return 0;
	}
        
        function updateNotificationInterval(current_user, notification_interval) {
		$.ajax({
			async : false,
			url : "./?url=ajax/updateNotificationInterval&user_id=" + current_user + "&notification_interval=" + notification_interval,
			success : function() {
				
			}
		});
	}
	
	function getNotificationInterval() {
		return $("#notification-interval").val();
	}
	
	function hideProfileEditLink() {
		$('#profile-edit-link').hide();
	}
       	
	function submit() {
		notification_interval = getNotificationInterval();
		updateNotificationInterval(current_user, notification_interval);
	}
	function sendMsgDialog(receiver_id,receiver_name){
		$('#modal_msg #receiver_id').find(':selected').val(receiver_id);
		$('#modal_msg #receiver_id').find(':selected').text(receiver_name);
		$('#modal_msg #msg_title').val('');
		$('#modal_msg #msg_body').val('');
		$('#modal_msg').modal('show');
	}
	function send_msg(){
		$.ajax({
			url : './?url=ajax/sendMsg&receiver_id=' + $('#modal_msg #receiver_id').val() +'&title='+encodeURIComponent($('#modal_msg #msg_title').val())+'&content='+encodeURIComponent($('#modal_msg #msg_body').val()),
			success : function(msg) {
				$('#modal_msg').modal('hide');
				$('#msg_response').show();
			}
		});		
		
	}
        function getProfile(user_id){
            $.ajax({
                url: './?url=ajax/getProfile&id=' + user_id,
                success: function(msg){
                   var data = eval('(' + msg + ')');                   
                   $('#full_profile').empty().append($('#full_profile_script').tmpl(data));
                   if (data.description){
                       $('#description_div').html(data.description);
                   }                   
                }
            })
        }
        function getFollowedProfile(user_id){
            $.ajax({
                url: './?url=ajax/getFollowedProfile&id=' + user_id,
                success: function(msg){
                   var data = eval('(' + msg + ')');
                   $('#followed_profile').empty()
                   for (var i in data){
                       $('#followed_profile').append($('#followed_profile_script').tmpl(data[i]));
                   }
                }
            })
        }
        function followers_hide(){
            $('#followers').hide();
        }
        function filterFollowers(user_id,filter){
            var task = getFollowersProfile(user_id);
            $.when(task).then(function(msg){
                var data = eval('(' + msg + ')');                 
                $('#followers_profile').empty();
                var res = [];
                if (filter.length > 0) {
                    var match = new RegExp("^" + filter, "i");          
                    for (var i in data){
                        if((data[i].username && data[i].username.match(match)) || (data[i].first_name && data[i].first_name.match(match)) || (data[i].last_name && data[i].last_name.match(match))){
                            res.push(data[i]);
                        }                               
                    }
                }else{
                    for (var k in data){
                        if(data[k].username ){
                            res.push(data[k]);
                        }                               
                    }                       
                } 
                var div_numb = 0;
                $('#followers_profile').append('<div class="row" id = "row'+ div_numb +'">');
                for (var j in res){var current_row = $('#followers_profile').find('#row' + div_numb);
                    current_row.append($('#followers_profile_script').tmpl(res[j]));
                    if ((j+1)%3 == 0){
                        div_numb++;
                        $('#followers_profile').append('<div class="row" id = "row'+ div_numb +'">');
                    }                    
                }                
            });          
        }
        function filterDecks(user_id,filter){
            var task = getContributedDecks(user_id);
            $.when(task).then(function(msg){
                var data = eval('(' + msg + ')');                
                $('#decks_preview').empty();
                var res = [];
                if (filter.length > 0) {
                    var match = new RegExp(filter, "i");          
                    for (var i in data){
                        if(data[i].title && data[i].title.match(match)){
                            res.push(data[i]);
                        }                               
                    }
                }else{
                    for (var k in data){
                        if(data[k].title ){
                            res.push(data[k]);
                        }                               
                    }                       
                } 
                var div_numb = 0;
                $('#decks_preview').append('<div class="row" id = "row'+ div_numb +'">');
                for (var j in res){var current_row = $('#decks_preview').find('#row' + div_numb);
                    current_row.append($('#decks_preview_script').tmpl(res[j]));
                    if ((j+1)%3 == 0){
                        div_numb++;
                        $('#decks_preview').append('<div class="row" id = "row'+ div_numb +'">');
                    }                    
                }                
            });
        }
        function getContributedDecks(user_id){            
            var result = $.Deferred();
            if (contributed_decks_cache){ //global variable, see beginning of the page 
                result.resolve(contributed_decks_cache);
            }else{
                $.ajax({
                    url : './?url=ajax/getContributedDecks&id=' + user_id,
                    success : function(msg){
                        contributed_decks_cache = msg;
                        result.resolve(msg);
                    }
                })            
            }
            return result.promise();
        }
        function filterSlides(user_id,filter){
            var task = getContributedSlides(user_id);
            $.when(task).then(function(msg){
                var data = eval('(' + msg + ')'); 
                console.log(data);
                $('#slides_preview').empty();
                var res = [];
                if (filter.length > 0) {
                    var match = new RegExp(filter, "i");          
                    for (var i in data){
                        if((data[i].title && data[i].title.match(match)) || (data[i].content && data[i].content.match(match))){
                            res.push(data[i]);
                        }                               
                    }
                }else{
                    for (var k in data){
                        if(data[k].title ){
                            res.push(data[k]);
                        }                               
                    }                       
                } 
                var div_numb = 0;
                $('#slides_preview').append('<div class="row" id = "row'+ div_numb +'">');
                for (var j in res){
                    var current_row = $('#slides_preview').find('#row' + div_numb);
                    current_row.append($('#slides_preview_script').tmpl(res[j]));
                    if ((j+1)%3 == 0){
                        div_numb++;
                        $('#slides_preview').append('<div class="row" id = "row'+ div_numb +'">');
                    }                    
                }                
            });
        }
        function getContributedSlides(user_id){            
            var result = $.Deferred();
            if (contributed_slides_cache){ //global variable, see beginning of the page 
                result.resolve(contributed_slides_cache);
            }else{
                $.ajax({
                    url : './?url=ajax/getContributedSlides&id=' + user_id,
                    success : function(msg){
                        contributed_slides_cache = msg;
                        result.resolve(msg);
                    }
                })            
            }
            return result.promise();
        }
        
        function getFollowersProfile(user_id){
            $('#followers_profile').empty();
            var result = $.Deferred();
            if (followers_cache){ //global variable, see beginning of the page 
                result.resolve(followers_cache);
            }else{
                $.ajax({
                    url : './?url=ajax/getFollowersProfile&id=' + user_id,
                    success : function(msg){
                        followers_cache = msg;
                        result.resolve(msg);
                    }
                })            
            }
            return result.promise();            
        }
        function insertDescription(user_id){
            var textarea = "<textarea class='span7' id='description_txtarea'></textarea>";
            var link_field = '';
            if ($('#infodeck').size()){
                var link_url = $('#link_to_presentation_url').attr('deck_id');                
                link_field = "<div id='infodeck_div' class='profile_infodeck'><label>Link to presentation:</label>";
                //TODO: slug_title
                link_field+="<div class='input-prepend'><span class='add-on'>slidewiki.org/deck/</span><input class='span5' id='link_to_presentation' name='link_to_presentation' value='" + link_url + "'></div></div>"; 
                $('#infodeck').remove();
            }else{
                //TODO: slug_title
                link_field = "<div id='infodeck_div' class='profile_infodeck'><label>Link to presentation:</label><div class='input-prepend'><span class='add-on'>slidewiki.org/deck/</span><input class='span5' id='link_to_presentation' name='link_to_presentation'></div></div>"; 
            }
            var button = "<div style='clear:both;'><button class='btn primary' id='save_desc_button' onclick='saveDescription(" + user_id + ")'>Save</button></div>";
            $("#insertDescriptionButton").replaceWith(textarea + link_field + button);            
        }        
        function editDescription(user_id){          
            var textarea = "<textarea class='span7' id='description_txtarea'></textarea>";
            var link_field = '';
            if ($('#infodeck').size()){
                var link_url = $('#link_to_presentation_url').attr('deck_id');                
                link_field = "<div id='infodeck_div' class='profile_infodeck'><label>Link to presentation:</label>";
                //TODO: slug_title
                link_field+="<div class='input-prepend'><span class='add-on'>slidewiki.org/deck/</span><input class='span5' id='link_to_presentation' name='link_to_presentation' value='" + link_url + "'></div></div>"; 
                $('#infodeck').remove();
            }else{
                //TODO: slug_title
                link_field = "<div id='infodeck_div' class='profile_infodeck'><label>Link to presentation:</label><div class='input-prepend'><span class='add-on'>slidewiki.org/deck/</span><input class='span5' id='link_to_presentation' name='link_to_presentation'></div></div>"; 
            }            
            var text = $("#description_div").text().trim(); 
            text.replace(/<br>/g, "\r");
            var button = "<div style='clear:both;'><button class='btn primary' id='save_desc_button' onclick='saveDescription(" + user_id + ")'>Save</button></div>";
            $("#description_div").replaceWith(textarea + link_field + button);
            $("#description_txtarea").text(text);
        }
        function saveDescription(user_id){
            var description = $("#description_txtarea").val();
            var link = $('#link_to_presentation').val();
            $.ajax({
                url: './?url=ajax/saveDescription&description=' + encodeURIComponent(description) + '&link=' + encodeURIComponent(link) + '&user_id=' + user_id,
                success: function (msg){
                getProfile(user_id); 
                }
            })
        } 
        function getFollowedUsersList(user_id){
            $('#followed_users_list').empty();
            $.ajax({
                url : './?url=ajax/getFollowedProfile&id=' + user_id,                
                success : function(msg){
                    var data = eval("(" + msg + ")");
                    if (data.length){
                        $('#followed_users_list_script').tmpl(data).appendTo('#followed_users_list');
                        $('#followed_users_link').attr('onclick', "prepareUserNews(user_id,'','users')");
                        prepareUserNews(user_id,'','users');
                    }else{
                        $('#activity_stream_users').empty().append("You don't have any followed users");
                        $('#followed_users .list-panel').remove();
                        $('#followed_users .activity-stream').css('width','100%');
                        $('#followed_users .list-panel').remove();
                        $('#filter-array_users').remove();
                    }
                }
            })         
        }
        function getFollowedDecksList(user_id){
            $('#followed_decks_list').empty();
            $.ajax({
                url : './?url=ajax/getFollowedDecks&id=' + user_id,                
                success : function(msg){
                    var data = eval("(" + msg + ")");                    
                    if (data.length){
                        $('#followed_decks_list_script').tmpl(data).appendTo('#followed_decks_list');
                        $('#followed_decks_link').attr('onclick', "prepareUserNews(user_id,'','decks')");
                        prepareUserNews(user_id,'','decks');
                    }else{
                        $('#activity_stream_decks').empty().append("You don't have any followed decks");
                        $('#followed_decks .list-panel').remove();
                        $('#followed_decks .activity-stream').css('width','100%');
                        $('#followed_decks .list-panel').remove();
                        $('#filter-array_decks').remove();
                    }
                }
            })         
        }
        function getFollowedSlidesList(user_id){
            $('#followed_slides_list').empty();
            $.ajax({
                url : './?url=ajax/getFollowedSlides&id=' + user_id,                
                success : function(msg){
                    var data = eval("(" + msg + ")");                    
                    if (data.length){
                        $('#followed_slides_list_script').tmpl(data).appendTo('#followed_slides_list');
                        $('#followed_slides_link').attr('onclick', "prepareUserNews(user_id,'','slides')");
                        prepareUserNews(user_id,'','slides');
                    }else{
                        $('#activity_stream_slides').empty().append("You don't have any followed slides");
                        $('#followed_slides .list-panel').remove();
                        $('#followed_slides .activity-stream').css('width','100%');
                        $('#followed_slides .list-panel').remove();
                        $('#filter-array_slides').remove();
                    }
                }
            })         
        }
        function prepareUserNews(user_id, month, type){
            var filter = [];
            $('#filter-array' + '_' + type).find('.filter').each(function(index){
                var value = $(this).attr('filter');
                filter[index] = value;
            })
            var facet = [];
            var i = 0;
            switch (type){
                case 'users' : 
                    i = 0;
                    $('#followed_users_list').find('.in_the_list').each(function(){
                        if ($(this).attr('item_checked') == '1'){
                           var f_value = $(this).attr('item_id');
                           facet[i] = f_value; 
                           i++;
                        }                        
                    })                    
                    break;
                case 'decks' : 
                    i = 0;
                    $('#followed_decks_list').find('.in_the_list').each(function(){
                        if ($(this).attr('item_checked') == '1'){
                           var f_value = $(this).attr('item_id');
                           facet[i] = f_value; 
                        }                        
                    })                    
                    break;
                case 'slides' : 
                    i = 0;
                    $('#followed_slides_list').find('.in_the_list').each(function(){
                        if ($(this).attr('item_checked') == '1'){
                           var f_value = $(this).attr('item_id');
                           facet[i] = f_value; 
                        }                        
                    })                    
                    break;
           }
           getUserNews(user_id, month, filter, type, facet, '');
        }
        function getUserNews(user_id, month, filter, type, facet, portion){
            if (!portion){
                var cur_date = new Date(); 
                var cur_day = cur_date.getUTCDate();        
                portion = 4 - Math.floor(cur_day / 7);
            }
           if (month.length){
               $.ajax({
                    url: './?url=ajax/getUserNews&user_id=' + user_id + '&month=' + month + '&filter=' + filter + '&type=' +type + '&facet=' + facet + '&portion=' + portion,
                    success: function(msg){                   
                        var data = eval('(' + msg + ')');
                        var month_split = month.split('-');
                        var prev_month = '';
                        var prev_year = '';                      
                        var cur_year = month_split['0'];
                        var cur_month = month_split['1'];                        
                        var prev_month_string = '';
                        if (data.activities.length){                            
                            data.month = month;
                            data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                            if ($('#activity_stream'+ '_' + type).find('#' + month).length == 0){
                                $('#activity_stream'+ '_' + type).find('#stream_previous_month').remove();
                                $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"+ '_' + type));
                            }
                            for (var i in data.activities){
                                var mod = (i-0+3)%2;                                
                                if (mod != 0){
                                    data.activities[i].side = 'l';
                                }else{
                                    data.activities[i].side = 'r';
                                }
                                data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                                var data_string_div = $('#activity_stream'+ '_' + type).find('#' + month);
                                data.activities[i].add_class = '';
                                $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo(data_string_div);
                            }
                            if (portion==4){
                                portion = 1;
                                if (cur_month != '01'){
                                    prev_month = cur_month - 1; 
                                    prev_year = cur_year;
                                }else{
                                    prev_month = 12;
                                    prev_year = cur_year - 1;                                
                                }
                                if (prev_month < 10 ) {
                                    prev_month = '0' + prev_month;
                                }                        
                                prev_month_string = prev_year + '-' + prev_month + '-01';
                            }else{
                                portion++;
                                prev_month_string = month;
                            }
                            
                        }else{
                            if (portion == 4){
                                portion = 1;
                                if (cur_month != '01'){
                                prev_month = cur_month - 1; 
                                prev_year = cur_year;
                                }else{
                                    prev_month = 12;
                                    prev_year = cur_year - 1;                                
                                }
                                if (prev_month < 10 ) {
                                    prev_month = '0' + prev_month;
                                }
                                prev_month_string = prev_year + '-' + prev_month + '-01'; 
                            }else{
                                portion++;
                                prev_month_string = month;
                            }                            
                            getUserNews(user_id, prev_month_string, filter, type,facet,portion);
                        }                        
                        if ($('#activity_stream'+ '_' + type).find('.self_registered_in').length == 0){
                             $('#activity_stream'+ '_' + type).find('#stream_previous_month').attr('onclick','getUserNews("' + user_id + '","' + prev_month_string + '","' + filter + '","' + type + '","' + facet + '","' + portion + '");');
                        }else{
                             $('#activity_stream'+ '_' + type).find('#stream_previous_month').remove();
                        }
                    }
                })
           }else{
               $.ajax({
                    url: './?url=ajax/getUserNews&user_id=' + user_id + '&filter=' + filter + '&type=' + type + '&facet=' + facet + '&portion=' + portion,
                    success: function(msg){                        
                        var data = eval('(' + msg + ')');                        
                        var cur_date = new Date();                   
                        var new_month = '';                            
                        var cur_month = cur_date.getMonth() + 1;
                        if (cur_month < 10) {
                            cur_month = '0' + cur_month;
                        }
                        var new_year = '';
                        var cur_year = cur_date.getUTCFullYear(); 
                        var data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';
                        $('#activity_stream'+ '_' + type).empty();                        
                        if (data.activities.length){                            
                            data.month = data_string;
                            data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;                            
                            $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"+ '_' + type));
                            for (var i in data.activities){
                                var mod = (i-0+3)%2;                                
                                if (mod != 0){
                                    data.activities[i].side = 'l';
                                }else{
                                    data.activities[i].side = 'r';
                                }
                                data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                                var data_string_div = $('#activity_stream'+ '_' + type).find('#' + data_string);
                                data.activities[i].add_class = '';
                                $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo(data_string_div);
                            }
                            if (portion == 4){
                                portion = 1;
                                if (cur_month != '1'){
                                    new_month = cur_month - 1;
                                    cur_date.setMonth(new_month);                                
                                }else{
                                    new_month = 12;                            
                                    new_year = cur_year - 1;
                                    cur_date.setYear(new_year);
                                    cur_date.setMonth(new_month - 1);                                
                                }
                                var month = cur_date.getUTCMonth();
                                
                                if (month < 10) {
                                    month = '0' + month;
                                }
                                var year = cur_date.getUTCFullYear();
                                var prev_month_string = year + '-' + month + '-01';
                            }else{
                                portion++;
                                prev_month_string = data_string;
                            }                            
                        }else{
                            if (portion == 4){
                                portion = 1;
                                cur_month = cur_date.getMonth() + 1;
                                if (cur_month < 10) {
                                    cur_month = '0' + cur_month;
                                }
                                cur_year = cur_date.getUTCFullYear(); 
                                data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';
                                if (cur_month != '1'){
                                    new_month = cur_month - 1;
                                    cur_date.setMonth(new_month);                                
                                }else{
                                    new_month = 12;                            
                                    new_year = cur_year - 1;
                                    cur_date.setYear(new_year);
                                    cur_date.setMonth(new_month - 1);                                
                                }
                                month = cur_date.getUTCMonth();
                                if (month < 10) {
                                    month = '0' + month;
                                }
                                year = cur_date.getUTCFullYear();
                                prev_month_string = year + '-' + month + '-01';
                            }else{
                                prev_month_string = data_string;
                                portion++;
                            }                            
                            getUserNews(user_id,prev_month_string, filter, type,facet, portion);
                        }                        
                        if ($('#activity_stream'+ '_' + type).find('.self_registered_in').length == 0){
                            $('#activity_stream'+ '_' + type).find('#stream_previous_month').attr('onclick','getUserNews("' + user_id + '","' + prev_month_string + '","' + filter + '","' + type + '","' + facet + '","' + portion + '");');
                        }else{
                           $('#activity_stream'+ '_' + type).find('#stream_previous_month').remove();
                        }                     
                    }
                })
           }
        }
        function changeFacet(button){
            if (button.attr('item_checked') == '1'){
                button.attr('item_checked', '0'); 
            }else{
                button.attr('item_checked', '1'); 
            }
            $('#activity_stream').empty();
        }
        function applyFacet(button,user_id,type){
            changeFacet(button);
            prepareUserNews(user_id,'',type);
        }
        function applyFilterUserNews(button,user_id,type){
            changeFilter(button);
            prepareUserNews(user_id, '', type);
        }
        function applyFilterUserStream(button,user_id){
            changeFilter(button, user_id);
            var keywords = $('#keywords').val();
            if (keywords.length >= 3){
                searchStream('my', user_id);
            }else{
                getUserStream(user_id,'','');  
            }
            
        }
        
       function getUserStream(user_id, month, portion){
           var filter = [];
            $('#filter-array').find('.filter').each(function(index){
                var value = $(this).attr('filter');
                filter[index] = value;
            })
            if (!portion){
                var cur_date = new Date(); 
                    var cur_day = cur_date.getUTCDate();        
                    portion = 4 - Math.floor(cur_day / 7);
            }
           if (month.length){
               $.ajax({
                    url: './?url=ajax/getUserStream&user_id=' + user_id + '&month=' + month  + '&filter=' + filter + '&portion=' + portion,
                    success: function(msg){                   
                        var data = eval('(' + msg + ')');
                        var month_split = month.split('-');
                        var prev_month = '';
                        var prev_year = '';                      
                        var cur_year = month_split['0'];
                        var cur_month = month_split['1'];                        
                        var prev_month_string = '';
                        if (data.activities.length){
                            data.month = month;
                            data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                            if ($('#activity_stream').find('#' + month).length == 0){
                                $('#activity_stream').find('#stream_previous_month').remove();
                                $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"));
                            }
                            for (var i in data.activities){
                                var mod = (i-0+3)%2;                                
                                if (mod != 0){
                                    data.activities[i].side = 'l';
                                }else{
                                    data.activities[i].side = 'r';
                                }
                                data.activities[i].add_class = 'no_photo';
                                data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                                $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#' + month));
                            }
                            if (portion == 4){
                                portion = 1;
                                if (cur_month != '01'){
                                    prev_month = cur_month - 1; 
                                    prev_year = cur_year;
                                }else{
                                    prev_month = 12;
                                    prev_year = cur_year - 1;                                
                                }
                                if (prev_month < 10 ) {
                                    prev_month = '0' + prev_month;
                                }                        
                                prev_month_string = prev_year + '-' + prev_month + '-01';
                            }else{
                                portion++;
                                prev_month_string = month;
                            }                            
                        }else{
                            if (portion==4){
                                portion = 1;
                                if (cur_month != '01'){
                                    prev_month = cur_month - 1; 
                                    prev_year = cur_year;
                                }else{
                                    prev_month = 12;
                                    prev_year = cur_year - 1;                                
                                }
                                if (prev_month < 10 ) {
                                    prev_month = '0' + prev_month;
                                }
                                prev_month_string = prev_year + '-' + prev_month + '-01';
                            }else{
                                portion++;
                                prev_month_string = month;
                            }                             
                            getUserStream(user_id, prev_month_string, portion);
                        }                        
                        if ($('#activity_stream').find('.registered_in').length == 0){
                            $('#stream_previous_month').attr('onclick','getUserStream("' + user_id + '","' + prev_month_string + '","' + portion + '");');
                        }else{
                            $('#stream_previous_month').remove();
                        }
                    }
                })
           }else{
               $.ajax({
                    url: './?url=ajax/getUserStream&user_id=' + user_id  + '&filter=' + filter + '&portion=' + portion,
                    success: function(msg){                   
                        var data = eval('(' + msg + ')');                        
                        var cur_date = new Date();                   
                        var new_month = '';                            
                        var cur_month = cur_date.getMonth() + 1;
                        if (cur_month < 10) {
                            cur_month = '0' + cur_month;
                        }
                        $('#activity_stream').empty();
                        var new_year = '';
                        var cur_year = cur_date.getUTCFullYear(); 
                        var data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';                        
                        if (data.activities.length){
                            data.month = data_string;
                            data.current = getMonthName(parseInt(cur_month - 1)) + ', ' + cur_year;
                            if ($('#activity_stream').find('#' + data_string).length == 0){                                
                                $("#activity_stream_script").tmpl(data).appendTo($("#activity_stream"));
                            }
                            for (var i in data.activities){
                                var mod = (i-0+3)%2;                                
                                if (mod != 0){
                                    data.activities[i].side = 'l';
                                }else{
                                    data.activities[i].side = 'r';
                                }
                                data.activities[i].add_class = 'no_photo';
                                data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                                $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#' + data_string));
                            }
                            if (portion==4){
                                portion = 1;
                                if (cur_month != '1'){
                                    new_month = cur_month - 1;
                                    cur_date.setMonth(new_month);                                
                                }else{
                                    new_month = 12;                            
                                    new_year = cur_year - 1;
                                    cur_date.setYear(new_year);
                                    cur_date.setMonth(new_month - 1);                                
                                }
                                var month = cur_date.getUTCMonth();
                                if (month < 10) {
                                    month = '0' + month;
                                }
                                var year = cur_date.getUTCFullYear();
                                var prev_month_string = year + '-' + month + '-01';
                            }else{
                                portion++;
                                prev_month_string = data_string;
                            }                            
                        }else{
                            if (portion == 4){
                                portion = 1;
                                cur_month = cur_date.getMonth() + 1;
                                if (cur_month < 10) {
                                    cur_month = '0' + cur_month;
                                }
                                cur_year = cur_date.getUTCFullYear(); 
                                data_string = cur_year.toString() + '-' + cur_month.toString() + '-01';
                                if (cur_month != '1'){
                                    new_month = cur_month - 1;
                                    cur_date.setMonth(new_month);                                
                                }else{
                                    new_month = 12;                            
                                    new_year = cur_year - 1;
                                    cur_date.setYear(new_year);
                                    cur_date.setMonth(new_month - 1);                                
                                }
                                month = cur_date.getUTCMonth();
                                if (month < 10) {
                                    month = '0' + month;
                                }
                                year = cur_date.getUTCFullYear();
                                prev_month_string = year + '-' + month + '-01';
                            }else{
                                portion++;
                                prev_month_string = data_string;
                            }                          
                            getUserStream(user_id,prev_month_string);
                        }                        
                        if ($('#activity_stream').find('.registered_in').length == 0){
                            $('#stream_previous_month').attr('onclick','getUserStream("' + user_id + '","' + prev_month_string + '","' + portion+ '");');
                        }else{
                            $('#stream_previous_month').remove();
                        }                     
                    }
                })
           }
           
       }
       function searchStream(mode, user_id){
           var keywords = $('#keywords').val();
           if (keywords.length >= 3){
               var filter = [];
                $('#filter-array').find('.filter').each(function(index){
                    var value = $(this).attr('filter');
                    filter[index] = value;
                })
               $.ajax({
                   url : './?url=ajax/searchStream&mode=' + mode + '&keywords=' + keywords + '&user_id=' + user_id + '&filter=' + filter,
                   success: function(msg){
                       var data = eval('(' + msg + ')');
                       //var data_string = 'activity_stream_all';
                       $('#activity_stream').empty();                               
                       if (data.activities && data.activities.length){
                           for (var i in data.activities){
                            
                                var mod = (i-0+3)%2;                                
                                if (mod != 0){
                                    data.activities[i].side = 'l';
                                }else{
                                    data.activities[i].side = 'r';
                                }
                                data.activities[i].add_class = 'no_photo';
                                data.activities[i].timestamp = prettyDate(data.activities[i].timestamp);
                                $('#activity_' + data.activities[i].type).tmpl(data.activities[i]).appendTo($('#activity_stream'));
                            }
                       }else{
                           $('#activity_stream').append('<div> No activities were found </div>');
                       }                       
                   }
               })
           }else{
               if (!keywords.length){
                   getUserStream(user_id,'','');
               }
           }
       }
