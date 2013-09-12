<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// include bootstrap
require_once (ROOT . DS . 'application' . DS . 'config' . DS . 'config.php');
$app_id = FB_APP;
$app_secret = FB_SECRET;
$my_url = FB_URL;

session_start();
if (isset($_REQUEST['code'])){
    $code = $_REQUEST["code"];
}else{
    $code='';
}

if(empty($code)) {
    $_SESSION['state'] = md5(uniqid(rand(), TRUE)); // CSRF protection
    $dialog_url = "https://www.facebook.com/dialog/oauth?client_id=" 
    . $app_id . "&redirect_uri=" . urlencode($my_url) . "&state="
    . $_SESSION['state'] . "&scope=email,user_education_history,user_hometown,user_interests,user_likes,user_location,user_work_history";
    echo("<script> top.location.href='" . $dialog_url . "'</script>");
}

if($_SESSION['state'] && ($_SESSION['state'] === $_REQUEST['state'])) {
    $token_url = "https://graph.facebook.com/oauth/access_token?"
    . "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)
    . "&client_secret=" . $app_secret . "&code=" . $code;
    $response = file_get_contents($token_url);
    $params = null;
    parse_str($response, $params);

    $_SESSION['access_token'] = $params['access_token'];
    echo("<script> top.location.href='" . BASE_PATH . "'</script>");
}
else {
    echo("The state does not match. You may be a victim of CSRF.");
}

?>
