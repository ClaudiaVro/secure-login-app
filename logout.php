<?php
include('includes/header.php');
if(isset($_SESSION['user_id']) && isset($_POST['log_out']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
    session_unset();
    session_destroy();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    echo '<p class="text-success">You have logged out. You will be redirected to the main page in 3 seconds</p>';
    header("refresh: 3; index");
} else {
    header("Location: index");
}

?>
