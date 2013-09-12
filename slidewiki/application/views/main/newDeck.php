<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<!--tags input-->
<script src="libraries/frontend/tags-input/tagsinput.min.js"></script>
<link rel="stylesheet" type="text/css" href="libraries/frontend/tags-input/tagsinput.css" />

<script type="text/javascript">
    $(function(){
        $('#tags').tagsInput({width:'327px'});
    });
</script>
<header class="page-header">
	<h1>
		Create new Deck
	</h1>
</header>
	
<?php if($user['is_authorized']): ?>
	<form id="newdeck" action="./?url=main/newDeck" method="POST">
		<div class="clearfix">
			<label for="title">Title:</label>
			
			<div class="input">
				<input type="text" id="title" name="deck[title]" value="" class="span8" />
			</div>
		</div>
		
		<div class="clearfix">
			<label for="abstract">Abstract:</label>
			
			<div class="input">
				<textarea id = "abstract" name="deck[abstract]" class="span12"></textarea>
			</div>
		</div>
            <div class="clearfix">
			<label for="default_language">Language:</label> 
                        <input id="language_id" type="hidden" name="deck[language_id]" value="<?php echo $default_language['id']?>">
                        <input id="language_name" type="hidden" name="deck[language_name]" value="<?php echo $default_language['name']?>">
                        <div class="input" style="margin-top:8px;">
                            <span id="language_visible"> <?php echo $default_language['name']?> </span>
                            <a style="cursor: pointer;" onclick="getLanguagesList('#new_deck_languages')"><i id="lang_icon" class="icon-chevron-down"></i></a>
                        </div>
                               
			<script id="new_deck_languages" type="text/x-jquery-tmpl">
                            <div style="float:left;"><table>
                            {{each languages}}
                                <tr><td class="" name="" onclick="setLanguage('#newdeck',this.id)" id="${$value.language}">${$value.name}</td></tr>
                                {{if ($index + 1) % 10 == 0}}
                                    </table></div><div style="float:left"><table>
                                {{/if}}
                            {{/each}}
                            </table></div>                            
                        </script>
		</div>
		<div class="clearfix">
			<label for="tags">Tags:</label>
			
			<div class="input">
				<input id="tags" type="text" name="deck[tags]" value="" class="span8" />
			</div>
		</div>
		<div class="clearfix">
			<label for="slideNo">Number of Slides:</label>
			
			<div class="input">
				<select id="slideNo" name="deck[slideNo]" class="span2">
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
				</select>
			</div>
		</div>		
		<div class="clearfix">
			<label for="theme">Default theme:</label>
			
			<div class="input">
				<select id="theme" name="deck[theme]" class="span4">
					<?php foreach ($styles as $style){?>
						<option value="<?php echo $style['id'];?>"><?php echo $style['name'];?></option>
					<?php }?>
				</select>
			</div>
		</div>
		<div class="clearfix">
			<label for="visibility">Visibility:</label>
			<div class="input">
				<input type="radio" name="deck[visibility]" value="1"> visible 
				<input type="radio" name="deck[visibility]" value="0" checked> invisible
 				<span class="help-inline">prevents your deck to get appeared in SlideWiki home page</span>
			</div>
		</div>		
		<div class="actions">
                        <input type="submit" id="true_submit" name="submit" value="Submit" style="display:none;">
			<input type="button" onclick="submitNewDeck()" class="btn primary" name="submit" value="Submit" /> 
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
