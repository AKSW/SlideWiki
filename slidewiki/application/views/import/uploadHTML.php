<header class="page-header">
	<h1>
		Add new Deck
		<small>
			by uploading slides as .html
		</small>
	</h1>
</header>

<?php if($user['is_authorized']): ?>
	<form enctype="multipart/form-data" action="./?url=import/handleHTMLUpload" method="POST">
		<div class="clearfix">
			<label for="file">Please choose a file:</label>
			
			<div class="input">
				<input class="input-file" id="file" name="uploaded" type="file" accept="text/html,application/zip" />
			</div>
		</div>
		
		<div class="actions">
			<input class="btn primary" type="submit" value="Upload" />
		</div>
	</form>
<?php else: ?>
	<div class="alert-message error">
		<p>
			<strong>Please <a href="#login-register-modal" data-controls-modal="login-register-modal" data-backdrop="true" data-keyboard="true">Login</a>!</strong>
			You need to be authenticated in SlideWiki before you can create new presentation decks.
		</p>
	</div>    
<?php endif; ?>
