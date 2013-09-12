<?php error_reporting(0);?>
<!DOCTYPE html>
<html>
<head>
	<base href="<?php echo BASE_PATH?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta property="og:image" content="http://slidewiki.org/static/img/slidewiki_image.png"/>
	<title><?php if(!isset($page_title)):?>SlideWiki<?php else: echo $page_title; endif;?></title>
   <?php if(isset($page_keywords)):?><meta name="keywords" content="<?php echo $page_keywords; ?>"><?php endif;?>
   <?php if(isset($page_description)):?><meta name="description" content="<?php echo $page_description; ?>"><?php endif;?> 
   <?php if(isset($page_additional_headers)) echo $page_additional_headers; ?>
    <!-- jquery ui styles -->
    <link rel="stylesheet" href="libraries/frontend/jquery-ui/css/smoothness/jquery-ui-1.8.16.custom.css" type="text/css" media="all" /> 
    <!--link rel="stylesheet" href="libraries/frontend/jquery-ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" type="text/css" media="all" /-->    
	<!-- fancybox styles -->
	<link rel="stylesheet" href="libraries/frontend/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="all" />
	
	<!-- deck js styles -->
	<link rel="stylesheet" href="libraries/frontend/deck.js/core/deck.core.css" type="text/css" media="all" />
	<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/goto/deck.goto.css" media="all" />
	<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/menu/deck.menu.css" media="all" />
	<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/navigation/deck.navigation.css" media="all" />
	<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/status/deck.status.css" media="all" />
	<link rel="stylesheet" href="libraries/frontend/deck.js/extensions/hash/deck.hash.css" media="all" />
	
	<!-- twitter bootrap 1.3.0 styles -->
	<link rel="stylesheet" href="libraries/frontend/twitter-bootstrap/bootstrap.min.css" media="all" />
	
	<!-- main styles -->
    <link rel="stylesheet" href="static/css/overlay.css" type="text/css" media="all" />
    <link rel="stylesheet" href="static/css/main.css" type="text/css" media="all" />
	<!--activity stream stylesheet-->
    <link rel="stylesheet" href="static/css/stream.css" type="text/css" media="all" /> 
     
	<!-- jquery -->
	<script type="text/javascript" src="libraries/frontend/jquery.min.js"></script>
	
	<!-- fancybox -->
	<script type="text/javascript" src="libraries/frontend/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	
	<!-- jquery ui scripts are necessary for positioning (now it's only one package, 8kb )-->
	<script src="libraries/frontend/jquery-ui/js/jquery-ui-1.8.16.custom.min.js"></script >	
	
	<!-- auth script -->
	<script type="text/javascript" src="static/js/auth.js"></script>

	<!-- deck js scripts -->
	<script src="libraries/frontend/deck.js/modernizr.custom.js"></script>
	<script src="libraries/frontend/deck.js/core/deck.core.js"></script>
	<script src="libraries/frontend/deck.js/extensions/menu/deck.menu.js"></script>
	<script src="libraries/frontend/deck.js/extensions/goto/deck.goto.js"></script>
	<script src="libraries/frontend/deck.js/extensions/status/deck.status.js"></script>
	<script src="libraries/frontend/deck.js/extensions/navigation/deck.navigation.js"></script>
	<script src="libraries/frontend/deck.js/extensions/iframes/deck.iframes.js"></script>
	<!-- twitter bootrap 1.3.0 scripts -->
	<script type="text/javascript" src="libraries/frontend/twitter-bootstrap/js/bootstrap-dropdown.js"></script>
	<script type="text/javascript" src="libraries/frontend/twitter-bootstrap/js/bootstrap-tabs.js"></script>
	<script type="text/javascript" src="libraries/frontend/twitter-bootstrap/js/bootstrap-modal.js"></script>
	<script type="text/javascript" src="libraries/frontend/twitter-bootstrap/js/bootstrap-alerts.js"></script>
	<script type="text/javascript" src="libraries/frontend/twitter-bootstrap/js/bootstrap-twipsy.js"></script>
	<script type="text/javascript" src="libraries/frontend/twitter-bootstrap/js/bootstrap-popover.js"></script>
	<!-- router script -->
	<script type="text/javascript" src="static/js/router.js"></script>
	
	<!-- jQuery Cookie Plugin --> 
	<script type="text/javascript" src="libraries/frontend/jquery.cookie.js"></script>
	
	<!-- jQuery jstree Plugin --> 
	<script type="text/javascript" src="libraries/frontend/jstree/jquery.jstree.js"></script>
	
	<script src="libraries/frontend/jquery.linkify.min.js"></script> 	
	<!-- external function script -->
	<script type="text/javascript" src="static/js/functions.js"></script>
	
    <!-- enhance gui js -->
    <script type="text/javascript" src="static/js/gui.js"></script>	
	<!-- MathJax -->
	<script type="text/javascript" src="libraries/frontend/MathJax/MathJax.js?config=default"></script>
	
	<!-- Feedback tab -->
	<script type="text/javascript" src="static/js/feedback.js"></script>
	
	<!-- Registration and login scripts -->
	<script type="text/javascript" src="static/js/userControl.js"></script>
     <!-- Translation -->
     <script type="text/javascript" src="static/js/translation.js"></script>
     <!-- check the browserType -->
     <script type="text/javascript" src="libraries/frontend/impressionist/scripts/utilities.js"></script>  
</head>
<body<?php echo isset($body_classes)?' class="'.$body_classes.'"':''; ?>>
<!-- Google Translation API-->
<script>
    function load() {
        gapi.client.setApiKey('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
    }
</script>
<script src="https://apis.google.com/js/client.js?onload=load"></script>

<!-- Languages table -->
        
<p id="ajax_progress_indicator"><img src='static/img/loader.gif' alt=""/><br/>Loading data</p>
<header> <!-- page header -->
    <nav>
        <div class="topbar">
            <div class="topbar-inner">
                <div class="container-fluid" id="top-toolbar">
                    <a href="./" class="brand">
                    <span class="r_entity r_webpage" itemscope itemtype="http://schema.org/WebPage"><span class="r_prop r_name" itemprop="name">
					SlideWiki
					</span><meta itemprop="description" content="SlideWiki aims to exploit the wisdom, creativity and productivity of the crowd for the creation of qualitative, rich, engaging educational content." /><meta itemprop="image" content="http://slidewiki.org/static/img/slidewiki_logo.png" /><meta itemprop="url" content="http://slidewiki.org" /><span itemprop="about" itemscope itemtype="http://schema.org/Thing"><meta itemprop="description" content="SlideWiki empowers communities of instructors, teachers, lecturers, academics to create, share and re-use sophisticated educational content in a truly collaborative way." /><meta itemprop="url" content="http://slidewiki.org/documentation/" /></span><span itemprop="author" itemscope itemtype="http://schema.org/Organization"><meta itemprop="description" content="The Research Group Agile Knowledge Engineering and Semantic Web (AKSW) is hosted by the Chair of Business Information Systems (BIS) of the Institute of Computer Science (IfI) / University of Leipzig as well as the Institute for Applied Informatics (InfAI)." /><meta itemprop="image" content="http://aksw.org/extensions/site/sites//local/images/logo-aksw.png" /><meta itemprop="name" content="AKSW" /><meta itemprop="url" content="http://aksw.org" /><span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><meta itemprop="description" content="Leipzig University Faculty of Mathematics and Computer Science,Institute of Computer Science,Dept. Business Information Systems" /><meta itemprop="faxNumber" content="+49 (341) 97 32 369" /><meta itemprop="telephone" content="+49 (341) 97 32 310" /><meta itemprop="addressCountry" content="Germany" /><meta itemprop="addressLocality" content="Leipzig" /><meta itemprop="postalCode" content="04109" /><meta itemprop="streetAddress" content="Augustusplatz 10" /></span></span>
					</span>
					</a>
                    <!-- insert main navigation here -->
                    <form class="form-search pull-left" method="get" id="search_header" onSubmit="return searchSubmitForm(this.keywords.value)">
                        <input type="text" name="keywords" placeholder="Search" class="input-medium search-query"/>
                        <button class="btn small" type="submit">Search</button>
                    </form>
                    <ul class="nav">
                    <li class="dropdown" style="margin-left:5px;">
                    	<a href="learning-menu" class="dropdown-toggle">About</a>
                    	<ul class="dropdown-menu">
                    		<li><a href="documentation/">Documentation</a></li>
                    		<li><a href="http://blog.aksw.org/category/projects/slidewiki/">News</a></li>
                    		<li><a href="https://groups.google.com/d/forum/slidewiki">Mailing list</a></li>
                    		<li><a href="slide/18/latest">Get involved</a></li>
                    		<li><a href="slide/57/latest">Cite SlideWiki</a></li>
                    		<li><a href="http://www.youtube.com/playlist?list=PL0A114817816A7849">Youtube channel</a></li>
                    		<li><a href="https://bitbucket.org/yamalightz/slidewiki/issues">Issue tracker</a></li>
                                <li><a href="slide/71/latest">Supporting organizations</a></li>
                        </ul>
                    </li>
                    </ul>
                    <ul class="nav secondary-nav">
                        <?php if ($user['is_authorized']): ?>
                        <li class="dropdown">
                            <a href="learning-menu" class="dropdown-toggle">Learning</a>
                            <ul class="dropdown-menu" id="learning-menu">
                                <li><a href="user/<?php echo $user['id']; ?>/scores/">View your scores</a></li>
                                <li><a id="manage_tests" href="user/<?php echo $user['id']?>/tests/">Manage your tests</a></li>
<!--                                <li><a href="tests/type/user">Search for test</a></li>-->
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#action-menu" class="dropdown-toggle">Add Deck</a>
                            <ul class="dropdown-menu" id="user-menu">
                                <li><a href="import/pptx">Upload slides (,pptx)</a></li>
                                <li><a href="import/html">Upload slides (deck.js, .html)</a></li>
                                <!-- li><a href="import/latex">Upload slides (.tex)</a></li-->
                                <li><a href="deck/create">Create empty deck</a></li>
                            </ul>
                        <li class="dropdown">
                            <a href="#user-menu" class="dropdown-toggle">Logged in as <?php echo $user['name']; ?></a>
                            <ul class="dropdown-menu" id="user-menu">                                
                                <li><a href="user/<?php echo $user['id']; ?>">Profile</a></li>
                                <li><a href="user/feed">News Feed</a></li>
                                <li><a style="cursor:pointer;" onclick="logout();">Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li><a href="#login-register-modal" data-controls-modal="login-register-modal" data-backdrop="true" data-keyboard="true">Login or Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div><!-- /.topbar -->
            <div class="editor-bar">
                <div class="topbar-inner">
                    <div class="container-fluid" id="editor-toolbar"></div>
                    <div id="active_editor_id" style="display:none;"></div>
                </div>			
            </div> 
        </div>
    </nav>
</header> <!-- /.page header -->
<section class="container"> <!-- main view -->
    <div id="languages" class="modal languages_table" style="display:none; width:auto;"></div>
    
    

