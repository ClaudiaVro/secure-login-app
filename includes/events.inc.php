<?php

// CSRF token function

function generate_token():string{
    return bin2hex(random_bytes(32));
}

function check_token($token){
    if($_SESSION['user_token'] == $token){
        return true;
    } 
    return false;
}

function destroy_token():void{
    unset($_SESSION['user_token']);
}

// check User Level

function check_user_lvl($page_lvl = 0){
    if(!isset($_SESSION['user_level'])){
        if($page_lvl != 0){
            log_to_file('Blocked attempt to enter admin page.');
            header('Location: index');
        }
    } else {
        if($_SESSION['user_level'] < $page_lvl){
            log_to_file('Blocked attempt to enter admin page.', $_SESSION['user_id']);
            header('Location: index');
        }
    }
}

// Log function

function log_to_file($custom_message = 'Unknown Message', $user = ''):bool{
    $data = $_SERVER['REQUEST_URI'] ." ". date("Y-m-d\TH:i:s",$_SERVER['REQUEST_TIME']) ." ". $_SERVER['REMOTE_ADDR'] . " ".$custom_message . " " .$user. "\n";
    return file_put_contents('../../log_events.txt', $data, FILE_APPEND);
}



?>