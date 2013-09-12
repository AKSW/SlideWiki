<?php 
if($alert):
?>
<header class="page-header">
	<h1>
		Create a new Brand
	</h1>
</header>
	
<?php if($user['is_authorized']): ?>
	<form id="newbrand" action="./?url=brand/create" method="POST">	
		<div class="clearfix">
			<label for="text">Text:</label>
			<div class="input">
				<textarea id = "text" name="brand[text]" class="span12"></textarea>
			</div>
		</div>	
		<div class="clearfix">
			<label for="image">Image:</label>
			
			<div class="input">
				<input type="text" id="title" name="brand[image]" value="" class="span8" />
			</div>
		</div>	
		<div class="clearfix">
			<label for="title">URL:</label>
			
			<div class="input">
				<input type="text" id="url" name="brand[url]" value="" class="span8" />
			</div>
		</div>	
		<div class="clearfix">
			<label for="deck">Deck ID:</label>
			
			<div class="input">
				<input type="text" id="deck" name="brand[deck]" value="" class="span8" />
			</div>
		</div>					
		<div class="actions">
			<input type="submit" class="btn primary" name="submit" value="Submit" /> 
		</div>
	</form>
<?php else: ?>
	<div class="alert-message error">
		<p>
			<strong>Please <a href="#login-register-modal" data-controls-modal="login-register-modal" data-backdrop="true" data-keyboard="true"><b>Login</b></a> or <a href="#login-register-modal" data-controls-modal="login-register-modal" data-backdrop="true" data-keyboard="true"><b>Register as a new user</b></a>.</strong>
			You need to be authenticated in SlideWiki before you can create new presentation decks.
		</p>
	</div>   
			<br><br><br><br><br><br><br><br><br> <br><br><br><br><br>
<?php endif; ?>
<?php else: ?>
<div class="alert alert-block  alert-warning fade in" data-alert="alert">
    <h2>Permission denied!</h2>
</div>
<?php endif;?>
