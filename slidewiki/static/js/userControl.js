function loginSubmit() {
    var login = document.loginform.login.value;
    var pass = document.loginform.password.value;	
    $.ajax({
        url : './?url=ajax/login&login='+login+'&pass='+pass,
        success : function(msg) {            
                if (msg==-1) {
                    $('#email').addClass('error');
                    $('#password').addClass('error');
                } else {
                    window.location.reload();

                }
        }
    })
}


function logout() {    
	$.ajax({
		url : './?url=ajax/logout',
		success : function() {
                    window.location.reload();
//                    var task = getFBStatus();
//                    $.when(task).then(function(msg) {
//                        if (msg.status == 'connected'){
//                            //TODO how we can determine that user id logged in via FB?
//                            window.location.reload();                          
//                        }else{
//                            window.location.reload();
//                        }
//                    });                   
		}
	})
}
function registerSubmit() {
	$('#reg_email').removeClass('error');
	$('#confirm_reg_email').removeClass('error');
	$('#captcha').removeClass('error');
	$('#username').removeClass('error');
        $('#confirm_password_div').removeClass('error');
        $('#reg_username_span').html('');
        $('#reg_confirm_span').html('');
        $('#reg_password_span').html('');
        $('#reg_verifypassword_span').html('');
        $('#reg_captcha_span').html('');
        $('#reg_addresse_span').html('');
	var login = document.registerform.login.value;
	var confirm_login = document.registerform.confirm_login.value;
	var username = document.registerform.username.value;
	var pass = document.registerform.password.value;
        var confirm_pass = document.registerform.verifypassword.value;
	var captcha = document.registerform.captcha.value;
        var error = false;
        if (login == ''){
            $('#reg_email').addClass('error');
            $('#reg_addresse_span').html('Invalid Email');
            error = true;
        }
        if (confirm_login != login){
            $('#confirm_reg_email').addClass('error');
            $('#reg_confirm_span').html('The confirmation Email differs from original');
            error = true;
        }
        if (username == ''){
            $('#username').addClass('error');
            $('#reg_username_span').html('Invalid username');
            error = true;
        }
        if (pass != confirm_pass){
            $('#confirm_password_div').addClass('error');
            $('#reg_verifypassword_span').html('The confirmation password differs from original');
            error = true;
        }
        if (!error){
            $.ajax({
		url : './?url=ajax/register&login='+encodeURIComponent(login)+'&username='+encodeURIComponent(username)+'&pass='+encodeURIComponent(pass)+'&captcha='+captcha,
		success : function(msg) {
                    if (msg==0){
                            window.location.reload();
                    } else {
                            if (msg.search('2')!=-1) {
                            $('#reg_email').addClass('error');
                            $('#reg_addresse_span').html('Invalid Email');
                        }
                        if (msg.search('5')!=-1){
                            $('#reg_email').addClass('error');
                            $('#reg_addresse_span').html('The chosen e-mail addresse is already in use');
                        }
                        if (msg.search('3')!=-1){
                            $('#username').addClass('error');
                            $('#reg_username_span').html('Invalid username');				
                        }
                        if (msg.search('6')!=-1){
                            $('#username').addClass('error');
                            $('#reg_username_span').html('The chosen username is already in use');
                        }
                        if (msg.search('1')!=-1){
                            $('#captcha').addClass('error');
                            $('#reg_captcha_span').html('Wrong combination');
                        }
//                        if (msg.search('7')!=-1){
//                            $('#reg_email').addClass('error');
//                            $('#captcha').addClass('error');
//                        }
//                        if (msg.search('4')!=-1){
//                            $('#confirm_reg_email').addClass('error');
//                            $('#reg_confirm_span').html('The confirmation Email differs from original');
//                        }
//                        if (msg.search('8')!=-1){
//                            $('#confirm_password_div').addClass('error');
//                            $('#reg_verifypassword_span').html('The confirmation password differs from original');
//                        }
                        var seed = Math.random()*100;
                        $('#captcha_img').empty();
                        $('#captcha_img').html('<img src="./?url=ajax/captcha#'+seed+'" alt=""/>');
                    }
		}
            })
        }else{
            var seed = Math.random()*100;
            $('#captcha_img').empty();
            $('#captcha_img').html('<img src="./?url=ajax/captcha#'+seed+'" alt=""/>');
        }
	
}

/*------------------------password recovery------------------------*/
function pass_recovery_email(){
    $('#login-register-modal').modal('hide');
    $('#pass_recovery_email').modal('show');
    
}
function pass_recovery_username(){
    $('#pass_recovery_email').modal('hide');
    $('#pass_recovery_username').modal('show');
}
function send_password(mode){
    var userOrMail='';
    if(mode=='email'){
        userOrMail = $('#email_recovery').val();
        password_recovery(userOrMail);
    }else {
        if (mode=='username'){
            userOrMail = $('#username_recovery').val();
            password_recovery(userOrMail);
        }else{
            document.location='./';
        }
    }    
}
function password_recovery(userOrMail){
    $.ajax({
        url : './?url=ajax/passwordRecovery&userOrMail=' + userOrMail,
        success : function(msg){
            $('#pass_recovery_email').modal('hide');
            $('#pass_recovery_username').modal('hide');
            alert('The password recovery instruction was sent to your registrattion e-mail');
        }
    })
}