<?php

class FacebookController extends Controller {
	function login() {
            
            $code='';    
            if (isset($_GET['code'])){
                $code = $_GET['code'];                    
            }
            if (!isset($_SESSION)){
                session_start();
            }
            $token_url = "https://graph.facebook.com/oauth/access_token?"
            . "client_id=" . FB_APP . "&redirect_uri=" . urlencode(FB_URL)
            . "&client_secret=" . FB_SECRET . "&code=" . $code;
            $response = file_get_contents($token_url);
            $params = null;
            parse_str($response, $params);

            $_SESSION['access_token'] = $params['access_token'];
            $graph_url = "https://graph.facebook.com/me?access_token=" . $_SESSION['access_token'];
            $fb_user = json_decode(file_get_contents($graph_url));
            
            $user = new User();
            //adding the fb_id to the existing email and login
            if (!($user->checkLogin($fb_user->email))){
                $user->id = $user->getIDByEmail($fb_user->email);
                $user->username = $user->getUsername();
                $user->addFBID($fb_user->id);
                $user->fb_id = $fb_user->id;
                $auth = new SlideWikiAuth($user);
                $auth->login();
                echo("<script> top.location.href='" . BASE_PATH . "'</script>"); 
            }else{
                $this->set('fb_user',$fb_user);
            }          
            

	}
}
