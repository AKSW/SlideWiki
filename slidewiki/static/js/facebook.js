function register_by_fb(){
    $('#fb_form #fb_confirm').removeClass('error');
    $('#fb_form #reg_verifypassword_span').html('');
    var password = $('#fb_form #fb_password').val();
    var confirm = $('#fb_form #fb_confirmation').val();
    var login = $('#fb_form #fb_login').val();
    var username = $('#fb_form #fb_username').val();
    var fb_id = $('#fb_form #fb_id').val();    
    if (password == confirm){        
        $.ajax({
            url: './?url=ajax/register&login='+encodeURIComponent(login)+'&username='+encodeURIComponent(username)+'&pass='+encodeURIComponent(password)+'&fb_id='+fb_id,
            success: function(msg){                
                if (msg==0){
                    top.location.href='./';
                }
            }
        });        
    }else{
        $('#fb_login_password #fb_confirm').addClass('error');
        $('#fb_login_password #reg_verifypassword_span').html('The confirmation password differs from original');
    }
}
function authNoPassword(fb_id,id){
    $.ajax({
        url: './?url=ajax/login&fb_id=' + fb_id + '&id=' + id,
        success : function (msg){
            window.location.reload();
        }
    })
}

function showUsersWithSameFB(fb_id){
    $.ajax({
        url: './?url=ajax/getUsersSameFB&fb_id=' +fb_id,
        success: function(msg){            
            var data = eval('('+msg+')');
            $('#user_list').empty();
            for (var i in data){
                data[i].fb_id = fb_id;
                $('#user_list').append($('#choose_user_script').tmpl(data[i]));
            }            
            $('#choose_user').modal('show');
        }
    })
}
function facebook_login() {
    FB.getLoginStatus(function(response) {
       if (response.status!='not_authorized') {
            var fb_id = response.authResponse.userID;
            $.ajax({
                url: './?url=ajax/checkFBId&fb_id=' + fb_id,
                async: false,
                success : function(msg){
                    var data = eval('('+msg+')');
                    if(data == true){
                        $.ajax({
                            url : './?url=ajax/login&fb_id='+fb_id,
                            async: false,
                            success : function(msg) {
                                var data = eval('('+msg+')');
                                if (!data){ //logi is successful
                                    window.location.reload();
                                }else{ 
                                    if (data == -2){//there are several users with the same fb_id
                                        showUsersWithSameFB(fb_id);
                                    } 
                                }                                                                                     
                            }
                        })
                    }else{
                        getRegistrationData();
                    }
                }
            });
        }else{
            alert('SlideWiki needs your permissions to fill out your profile');
            getRegistrationData();
        }
    })
}
    
//we do not use yet
function facebook_logout() {
    FB.logout(function(response) {
        alert('logout');
    });
}

function getRegistrationData(){    
    $.ajax({
        url : './?url=ajax/getFB_DATA',
        async: false,
        success : function(msg){
            var data = eval('('+msg+')');
            var id = data.id;
            var url = data.url;              
            var dialog_url = "https://www.facebook.com/dialog/oauth?client_id=" + id + "&redirect_uri=" + url + "&response_type=code&scope=email,user_birthday,user_education_history,user_hometown,user_interests,user_likes,user_location,user_work_history";
            top.location.href = dialog_url;
        }
    })  
}
function getFBProfileData(){
    FB.api('/me?fields=email,birthday,id,first_name,middle_name,last_name,gender,locale,link,username,education,hometown,location,languages,picture,work,interests', function(response) {
        var big_picture = "https://graph.facebook.com/" + response.username + "/picture?type=large";
        response.big_picture = big_picture;        
        $('#full_profile').empty().append($('#full_profile_script').tmpl(response));        
    })
}
function getFBProfile(){    
    FB.login(function(response) {
        if (response.authResponse) {
            var fb_id = response.authResponse.userID;
            $.ajax({
                url:'./?url=ajax/getUserFBId',
                success : function(msg){
                    var user_fb_id = eval ('('+msg+')');                    
                    if (user_fb_id > 0){  //if user has already assigned the facebook account                    
                        if (user_fb_id != fb_id){
                            alert('Your SlideWiki account is assigned with another facebook profile.');
                        }else{
                            getFBProfileData();
                        }                        
                    }else{
                        $.ajax({
                            url: './?url=ajax/checkFBId&fb_id=' + fb_id,
                            success : function(msg){
                                var data = eval('('+msg+')');
                                if(data == true){
                                    $.ajax({
                                        url : './?url=ajax/MergeUsers&fb_id='+fb_id,
                                        success : function(msg) {                                
                                            getFBProfileData();                                                 
                                        }
                                    })
                                }else{
                                    var answer = confirm("Leave page to login with Facebook account?")
                                    if (answer){
                                        getRegistrationData();
                                    }                        
                                }
                            }
                        })
                    }
                }
            })
                        
        } else {
           alert('Unknown error occured. Please, try later');
        }
    });    
}
//for asking whether we should logout from facebook - do not use yet
function FBLogoutAsk(){
    alert(12);
}
//check if the user is logged in facebook - do not use yet
function getFBStatus(){
    
    var result = $.Deferred();
    FB.getLoginStatus(function(response) {
        result.resolve(response);
    });
    return result.promise();
}


