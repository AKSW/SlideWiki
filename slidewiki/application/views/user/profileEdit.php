<script type="text/javascript" src="static/js/profile.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>

<?php if($authorized) { ?>

<div class="content">
    <div class="page-header">
        <h1>
            <?php if ($email): ?>
                <img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($email))); ?>?d=retro&amp;r=g" height="50" width="50" class="avatar" />
            <?php endif; ?>
			<!-- Get to the profile page by clicking on the username -->
			<!-- TODO: @haschke - change link style -->
            <a href="user/<?php echo $_GET['id']; ?>"><?php echo trim($profile->username);?></a> 
            <?php if (($_GET['id'] != $user['id'])): ?>
            <small>
                <?php if (($user['is_authorized'] && $_GET['id'] != $user['id']) && $isFollowing): ?>
                    <a href="#" class="btn small danger" onclick="follow($(this),'user',<?php echo $_GET['id'];?>)">Unfollow user</a>
                <?php endif; ?>
                <?php if (($user['is_authorized'] && $_GET['id'] != $user['id']) && !$isFollowing): ?>
                    <a href="#" class="btn small success" onclick="follow($(this),'user',<?php echo $_GET['id'];?>)">Follow user</a>
                <?php endif; ?>
            </small>
            <?php endif; ?>
            
        </h1>
       
    </div>
        <div class="edit_profile" id="edit_profile">
            <header>
                <nav>
                    <ul class="tabs" data-tabs="tabs">
                        <li class="active" id="profile_settings"><a href="#settings">Account settings</a></li>
                        <li id="profile_profile"><a href="#profile" id="profile_tab_link" onclick="getProfile(<?php echo $_GET['id']; ?>)">Profile info</a></li>                            
                    </ul>
                </nav>                        
            </header> 
            <section class="tab-content">
                <div id="settings" class="active">
                    <form id="update-profile" class="form-stacked" action="./?url=ajax/saveSettings&id=<?php echo $_GET['id'];?>" method="POST">
                        <fieldset>
                            <div class="clearfix">
                            <label for="old">Old password:</label>
                                    <input id = "old" type="password" name="user[old_password]" value="">
                            </div>
                            <div class="clearfix">
                            <label for="new">New password:</label> 
                                    <input id="new" type="password" name="user[new_password]" value="">
                            </div>
                            <div class="clearfix">
                            <label for="confirm">Confirm new password:</label> 
                                    <input id="confirm" type="password" name="user[confirm_new_password]" value="">
                            </div>
                            <div class="clearfix">
                            <label for="default_language">Select preferred language of presentations:</label> 
                                    <input id="language_id" type="hidden" name="user[language_id]" value="<?php echo $default_language['id']?>">
                                    <input id="language_name" type="hidden" name="user[language_name]" value="<?php echo $default_language['name']?>">
                                    <div class="clearfix">
                                        <span id="language_visible"><?php echo $default_language['name']?></span>
                                        <a style="cursor: pointer;" onclick="getLanguagesList('#profile_languages')"><i id="lang_icon" class="icon-chevron-down"></i></a>
                                    </div>
                            
                                    <script id="profile_languages" type="text/x-jquery-tmpl">
                                <div style="float:left;"><table>
                                {{each languages}}
                                    <tr><td class="" name="" style="cursor:pointer" onclick="setLanguage('#update-profile',this.id)" id="${$value.language}">${$value.name}</td></tr>
                                    {{if ($index + 1) % 10 == 0}}
                                        </table></div><div style="float:left"><table>
                                    {{/if}}
                                {{/each}}
                                </table></div>                            
                            </script>
                            </div>
                            <div class="clearfix">
                            <label for="interval"> Notification interval</label>
                                    <!-- Need to make automatic notification interval retrieval -->
                                    <select id = "interval" name="user[notification_interval]">
                                            <option value="off" <?php if ($notification_interval=='off') echo 'selected';?>>OFF</option>
                                            <option value="hourly" <?php if($notification_interval=='hourly') echo 'selected';?>>Hourly</option>
                                            <option value="daily"<?php if($notification_interval=='daily') echo 'selected';?>>Daily</option>
                                            <option value="weekly" <?php if($notification_interval=='weekly') echo 'selected';?>>Weekly</option>
                                    </select>
                            </div>
                            <div class="actions">
                            <input class="btn small success" type="submit" name="submit" value="Submit" /> 
                            </div>
			</fieldset>
                    </form>
                </div>
                <div id="profile">
                    <div id="full_profile"></div>
                    <script id="full_profile_script" type="text/x-jquery-tmpl">
                        <form id="save_full_profile" class="form-stacked" action="./?url=ajax/saveProfile&id=<?php echo $_GET['id'];?>" method="POST">
                            <legend><h3>Your public profile: </h3></legend>
                            <fieldset>
                                <div class="row">
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[first_name]">First name:</label>
                                            <div class="input"> 
                                                <input name="user[first_name]" value="${first_name}">                                        
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span5">
                                        <div class="clearfix">
                                             <label for="user[last_name]">Last name:</label>
                                            <div class="input"> 
                                                <input name="user[last_name]" value="${last_name}">                                      
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[gender]">Gender:</label>
                                            <div class="input"> 
                                                <input type="radio" name="user[gender]" value="female"
                                                    {{if gender == 'female'}} checked {{/if}}
                                                > female
                                                <input type="radio" name="user[gender]" value="male"
                                                    {{if gender == 'male'}} checked {{/if}}
                                                > male
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[hometown]">Hometown:</label>
                                            <div class="input"> 
                                                <input name="user[hometown]" 
                                                   {{if hometown}}
                                                        {{if hometown.name}}  value = "${hometown.name}" 
                                                        {{else}} value = "${hometown}"
                                                        {{/if}}
                                                   {{/if}}
                                                   >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[location]">Location:</label>
                                            <div class="input"> 
                                                <input name="user[location]" 
                                                    {{if location}}
                                                        {{if location.name}}  value = "${location.name}" 
                                                        {{else}} value = "${location}"
                                                        {{/if}}
                                                   {{/if}}
                                               >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[location]">Locale:</label>
                                            <div class="input"> 
                                                <input name="user[locale]" value="${locale}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[picture]">Picture:</label>
                                            <div class="input"> 
                                                <input name="user[picture]"
                                                 {{if picture}}
                                                        {{if big_picture}}  value = "${big_picture}" 
                                                        {{else}} value = "${picture}"
                                                        {{/if}}
                                                 {{/if}}     
                                               >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[birthday]">Birthday:</label>
                                            <div class="input"> 
                                                <input name="user[birthday]" value = "${birthday}" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[infodeck]">Link to the presentation</label>
                                            <div class='input-prepend'><span class='add-on'>slidewiki.org/deck/</span>
                                                <input class="span2" name="user[infodeck]" value = "${infodeck}" >
                                            </div>
                                        </div>
                                    </div>
                                </div>                                
<!--                                <div class="row">
                                    <div class="span5">
                                        <div class="clearfix">
                                            <label for="user[languages]">Languages:</label>
                                            <div class="input"> 
                                                <textarea name="user[languages]">
                                                    {{each languages}}
                                                        ${languages.id},
                                                    {{/each}}
                                                </textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>-->
                            </fieldset>
                            
                            <div class="actions">
                                <button class="btn primary" type="submit">Save</button>
                            </div>
                        </form>
                    </script>
                </div>
            </section> 
        </div>
    
 <!-- /.content -->
 
<?php } ?>
