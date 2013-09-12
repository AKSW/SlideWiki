<?php if (isset($fb_user)) : ?>
<div id="fb_login_password">
    <header class="modal-header">
        <a href="#" title="close window" class="close">×</a>
        <h3>Choose a password:</h3>
    </header>
    <div class="modal-body">
        <form id="fb_form" name="fb_form" class="tab-pane"  action="javascript:register_by_fb()">
        <fieldset>
        <div class="clearfix">
            <label for="fb_username">Username:</label>
            <div class="input">
                <input type="text" name="fb_username" id="fb_username" class="xlarge" value="<?php echo $fb_user->username; ?>">
            </div>
        </div>
        <div class="clearfix">
            <label for="fb_login">Email:</label>
            <div class="input">
                <input type="text" name="fb_login" id="fb_login" class="xlarge" value="<?php echo $fb_user->email; ?>">
            </div>
        </div>
        <input type="hidden" name="fb_id" id="fb_id" class="xlarge" value="<?php echo $fb_user->id; ?>">
        <div id="fb_pass" class="clearfix">
            <label for="fb_password">Choose a SlideWiki password:</label>
            <div class="input">
                <input type="password" name="fb_password" id="fb_password" class="xlarge" />                        
            </div>
        </div>
        <div id="fb_confirm" class="clearfix">
            <label for="fb_confirmation">Confirm the password</label>
            <div class="input">
                <input type="password" name="fb_confirmation" id="fb_confirmation" class="xlarge" /> 
                <span class="help-block" id="reg_verifypassword_span"></span>
            </div>
        </div>
         <div class="actions">
            <button type="submit" class="btn primary">Submit</button>
        </div>
       </fieldset> 
        </form>
    </div>
</div>
<?php endif; ?>