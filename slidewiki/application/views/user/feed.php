<?php require_once (ROOT . DS . 'application' . DS . 'views' . DS . 'activity_templates.php'); ?>
<script type="text/javascript" src="static/js/profile.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>


<script>
    getFollowedUsersList(<?php echo $current_user; ?>);
    getFollowedDecksList(<?php echo $current_user; ?>);
    getFollowedSlidesList(<?php echo $current_user; ?>); 
    
    
    
     
</script>


<article>
    <header>
        <div class="page-header row">
            <h2>Latest news</h2>
        </div>
        <nav>
            <ul class="tabs" data-tabs="tabs" id="item_tabs">                    
                <li class="active"><a href="#followed_users" id="followed_users_link" onclick="">Followed users</a></li>                   
                <li><a href="#followed_decks" id="followed_decks_link" onclick="">Followed decks</a></li>
                <li><a href="#followed_slides" id="followed_slides_link" onclick="">Followed slides</a></li>
            </ul>
        </nav>
    </header>    

    <section class="tab-content">    
    <div id="followed_users" class="active">        
        <nav>
            <div class="btn-toolbar primary">
                <div id="filter-array_users" style="width:auto;float:right;text-align:right;vertical-align:bottom;clear:both;display:inline;" class="btn-group">
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="1" class="btn small success filter">Follow activities</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="1" class="btn small success filter">Deck creation</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="0" class="btn small success filter">Slide creation</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="1" class="btn small success filter">Translation activities</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="1" class="btn small success filter">Comments</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="1" class="btn small success filter">Question creation</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'users')" filter="1" class="btn small success filter">Test results</a>
                </div>
            </div>
        </nav>           
        <div class="list-panel">
            <div id="followed_users_list"></div>
        </div>
        <div class="activity-stream">
            <article id="activity_stream_users"></article> 
        </div>
    </div>
    
    <div id="followed_decks">        
        <nav>
            <div class="btn-toolbar primary">
                <div id="filter-array_decks" style="width:auto;float:right;vertical-align:bottom;clear:both;display:inline;" class="btn-group">
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'decks')" filter="1" class="btn small success filter">Creation</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'decks')" filter="1" class="btn small success filter">Follow activities</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'decks')" filter="1" class="btn small success filter">Comments</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'decks')" filter="1" class="btn small success filter">Translations</a>
                </div>
            </div>
        </nav>           
        <div class="list-panel">
            <div id="followed_decks_list"></div>
        </div>
        <div class="activity-stream">
            <article  id="activity_stream_decks"></article> 
        </div>                   
    </div>
    <div id="followed_slides">       
        <nav>
            <div class="btn-toolbar primary">
                <div id="filter-array_slides" style="width:auto;float:right;vertical-align:bottom;clear:both;display:inline;" class="btn-group">
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'slides')" filter="1" class="btn small success filter">Creation</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'slides')" filter="1" class="btn small success filter">Follow activities</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'slides')" filter="1" class="btn small success filter">Comments</a>
                    <a onclick="applyFilterUserNews($(this), <?php echo $current_user; ?>,'slides')" filter="1" class="btn small success filter">Translations</a>
                </div>
            </div>
        </nav>         
        <div class="list-panel">
            <div id="followed_slides_list"></div>
        </div>
        <div class="activity-stream">
            <article  id="activity_stream_slides"></article> 
        </div>       
    </div>
	

</section>
</article>


 