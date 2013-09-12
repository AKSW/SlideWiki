<header class="page-header">
	<h1>
		Add new Deck
		<small>
			by uploading slides as .pptx
		</small>
	</h1>
</header>

<?php if($user['is_authorized']): ?>
	<form enctype="multipart/form-data" action="./?url=import/handleOOXMLUpload" method="POST">
		<div class="clearfix">
			<label for="file">Please choose a file:</label>
			
			<div class="input">
				<input class="input-file" id="file" name="uploaded" type="file" accept="application/vnd.openxmlformats-officedocument.presentationml.presentation" />
			</div>
		</div>
		
		<div class="clearfix" style="display:none;">
			<div class="input">
				<ul class="inputs-list">
					<li class="row">
						<div class="span1">
							<input type="checkbox" name="import_with_style" value="false" disabled/>
						</div>
						
						<div class="span10">
							<label>
								Import with styling
							</label>
						</div>
					</li>
				</ul>
			</div>
		</div>
		
		<div class="actions">
			<input class="btn primary" type="submit" value="Upload"/>
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
