<script id="activity_stream_script" type="text/x-jquery-tmpl">    
    <header>
        <h3>${current}</h3> 
    </header>
    <div style="clear:both; overflow:hidden;" id="${month}">           
    </div>
    <div><a style="cursor:pointer" id="stream_previous_month">Show more...</a></div>
</script>

<script id="activity_only_text" type="text/x-jquery-tmpl">    
<div class="home-activities-item">           
    <div class="activity-text"><a href="./user/${subject.id}">${subject.username}</a> 
    ${type_text} <a href="./deck/${object.id}_${object.slug_title}">${object.title}</a></div><br/>
	<div class="timestamp">${timestamp}</div>
</div>               
</script>

<script id="followed_users_list_script" type="text/x-jquery-tmpl">
    <div class="in_the_list" item_id="${id}" item_checked="1" onclick="applyFacet($(this),'3','users')">
        <div class="avarat-text user-picture-tiny">
            <img src="./?url=ajax/getAvatarSrc&id=${id}" width="15" class="avatar-tiny">
        </div>
        <div>               
        ${username}
        </div>        
    </div>    
</script>
<script id="short_deck_stream" type="text/x-jquery-tmpl">
    <header>
        <h3>Latest activity:</h3> 
    </header>
    <div style="clear:both; overflow:hidden;" id="short_activities">           
    </div>
    <div id="short_deck_stream_btn"><a style="cursor:pointer" class="btn mini" id="stream_previous_month" href="./?url=main/deck_stream&deck=${current_id}">Show more...</a></div>
</script>
<script id="followed_decks_list_script" type="text/x-jquery-tmpl">
    <div class="in_the_list" item_id="${deck_id}" item_checked="1" onclick="applyFacet($(this),'3','decks')">
        <div>               
        ${title}
        </div>        
    </div>    
</script>
<script id="followed_slides_list_script" type="text/x-jquery-tmpl">
    <div class="in_the_list" item_id="${slide_id}" item_checked="1" onclick="applyFacet($(this),'3','slides')">
         <div>               
        ${title}
        </div>      
    </div>    
</script>

<script id="activity_followed_user" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            started following <a href="./user/${object.id}">${object.username}</a> <br>
            <span class="timestamp">${timestamp}</span>            
        </div>
    </div>
</script>
<script id="activity_followed_deck" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            started following <a href="./deck/${object.id}_${object.slug_title}">${object.title}</a> deck <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_created_deck" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}" deck_id="${object.deck_id}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            created <a href="./deck/${object.id}_${object.slug_title}">${object.title}</a> deck <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_created_deck_revision" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            created <a href="./deck/${object.id}_${object.slug_title}">new revision</a> for <a href="./deck/${text.id}_${text.slug_title}">${text.title}</a> deck <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_translated_deck" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            translated <a href="./deck/${text.id}_${text.slug_title}">${text.title}</a> deck to <a href="./deck/${object.id}_${object.slug_title}">${object.language.name}</a><br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_translated_deck_from" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}" deck_id="${object.deck_id}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            translated <a href="./deck/${object.id}_${object.slug_title}">${object.title}</a> deck from <a href="./deck/${text.id}_${text.slug_title}">${text.language.name}</a><br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_translated_slide_from" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            translated <a href="./slide/${object.id}">${object.title}</a> slide from <a href="./slide/${text.id}">${text.language.name}</a><br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_followed_slide" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            started following <a href="./slide/${object.id}">${object.title}</a> slide<br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_created_slide_revision" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            created a new revision for <a href="./slide/${object.id}">${object.title}</a> slide<br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_created_slide" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            created <a href="./slide/${object.id}">${object.title}</a> slide<br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_translated_slide" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            translated <a href="./slide/${object.id}">${object.title}</a> slide to <a href="./slide/${text.id}">${text.language.name}</a><br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_translated_slide_revision" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            revised the translation of <a href="./slide/${object.id}">${object.title}</a> slide <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_commented_slide_revision" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            commented the <a href="./slide/${object.id}">${object.title}</a> slide <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_commented_deck_revision" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            commented the <a href="./deck/${object.id}_${object.slug_title}">${object.title}</a> deck <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_created_question_revision" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            updated question "${object.question}" for <a href="./slide/${text.id}">${text.title}</a> slide<br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_created_question" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            created new question "${object.question}" for <a href="./slide/${text.id}">${text.title}</a> slide<br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_answered_test" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            answered test for <a href="./deck/${object.id}_${object.slug_title}">"${object.title}"</a> course with <b>${text.score}</b> score<br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_registered_in" type="text/x-jquery-tmpl"> 
    <div class="activity ${type} ${add_class}" data-side="${side}">
        <div class="avarat-text user-picture ${add_class}">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar ${add_class}">
        </div>
        <div class="activity-text ${add_class}">               
            <a href="./user/${subject.id}">${subject.username}</a> 
            registered in SlideWiki <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
<script id="activity_self_registered_in" type="text/x-jquery-tmpl"> 
    <div class="activity ${type}" data-side="${side}">
        <div class="avarat-text user-picture">
            <img src="./?url=ajax/getAvatarSrc&id=${subject.id}" width="50" class="deck-owner-avatar">
        </div>
        <div class="activity-text">               
            You registered in SlideWiki <br>
            <span class="timestamp">${timestamp}</span>
        </div>
    </div>
</script>
