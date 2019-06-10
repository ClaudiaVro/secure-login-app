<?php 

define('LIVE', FALSE);
define('EMAIL', '');
define ('BASE_URL', $_SERVER['HTTP_HOST'] . '/login-sys-lic/');
define('MYSQL', '../../db.inc.php');
define('PASSWORD', '');
define('CURRENT_DATE', date('U'));
define('CATCHPHRASE', 'This page has the following security measures:');
date_default_timezone_set('Europe/Bucharest');

require('includes/events.inc.php');
if(isset($page_lvl)){
    check_user_lvl($page_lvl);
} else {
    check_user_lvl();
}
// checking last activity
if (isset($_SESSION['last_activity'], $_SESSION['user_id'])){
    if(time() - $_SESSION['last_activity'] > 10) {
        session_unset();   
        session_destroy();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
        header("Location: index");
    } else {
        $_SESSION['last_activity'] = time();
    }
}


function my_error_handler($e_number, $e_message, $e_file, $e_line, $e_vars){
    $message = "An error occured in script '$e_file' on line $e_line: $e_message\n";
    if(!LIVE){
        echo '<div class="text-danger">'.nl2br($message).'</div>';
        echo '<pre>'.print_r($e_vars, 1). "\n";
        debug_print_backtrace();
        echo '</pre></div>';
    } else {
        include('includes/send_email.php');
        $body = $message . "\n" .print_r($e_vars, 1);
        send_email(EMAIL, $body);

        if($e_number != E_NOTICE){
        echo '<div class="text-danger">A system error occured. We apologize for the incovenience.</div><br>';
        }
    }
    
}

set_error_handler('my_error_handler');