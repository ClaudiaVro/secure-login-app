<?php

require('includes/config.inc.php');
require('includes/header.php');

if(isset($_GET['y']) && (strlen($_GET['y']) == 32)){
    require(MYSQL);
    $stmt = $dbc->stmt_init();
    $y = $_GET['y'];
    $q = "UPDATE `users-lic` SET active = 1 WHERE register_token= ? LIMIT 1";
    $stmt->prepare($q);
    $stmt->bind_param("s", $y);
    $stmt->execute();
    $result = $stmt->get_result();
    if($stmt->affected_rows == 1){
        echo 'Your account is now active. You may now log in.';
        log_to_file('Account Active');

        $q = "UPDATE `users-lic` SET register_token = 0 where register_token= ? LIMIT 1";
        $stmt->prepare($q);
        $stmt->bind_param("s", $y);
        $stmt->execute();
    } else {
        echo '<p>Your account could not be activated. Please re-check the link.</p>';
        echo $stmt->error;
    }
    $dbc->close();
     
} else {
    //$url = BASE_URL;
    header("Location: index");
    exit();
}