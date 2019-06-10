<?php

require('includes/header.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $e = FALSE;
    $trimmed_email = trim($_POST['email']);
    if(!empty($trimmed_email)){
        if(filter_var($trimmed_email, FILTER_VALIDATE_EMAIL)){
            $e = $trimmed_email;
        } else {
            $email_err_msg = '<p class="text-danger" align="center">The email submitted is not valid.</p>';
        } 
    } else {
        $email_err_msg = '<p class="text-danger" align="center">Please input an email!</p>';
    }
    
    if($e){
        require(MYSQL);
        $q = "SELECT email from `users-lic` where email = ?";
        $stmt = $dbc->stmt_init();
        $stmt->prepare($q);
        $stmt->bind_param("s", $e);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows == 0){ // no users found
            $gen_msg = 'An email has been sent with futher instructions to reset password(no valid email).';
            $stmt->close();
        } else {
            $stmt->close();
            $current_date = CURRENT_DATE + 1800;         
            $token = bin2hex(random_bytes(32));

            $url = BASE_URL . 'generate_pwd?token=' . $token;
            $q = "UPDATE `users-lic` SET pw_reset_token = ?, pw_reset_timer = ? where email= ?";
            $stmt1 = $dbc->stmt_init();
            $stmt1->prepare($q);
            $stmt1->bind_param("sis",$token, $current_date, $e);
            $stmt1->execute();
            
            if($stmt1->affected_rows == 1){
                require('includes/send_email.php');
                // prepare email send
                $body = "Open this link to continue the reset password procedure.\n\n <strong>";
                $body .= $url . '</strong>';                
                if(send_email($e, $body)){
                    $gen_msg = 'An email has been sent with further instructions to reset password.';
                }
            }
        }
    }
    if(isset($gen_msg)){
        log_to_file($gen_msg);
    }

}


?>


<div class="row">
    <div class="col">
        <h1 align="center">Lost Password</h1>
        <form action="lost_pwd" method="post">
            <p>An email will be sent with further instructions.</p>
            <div class="form-group row">
                <label for="email" class="col-sm-3">Email Address:</label>
                <div class="col-sm-9">
                    <input type="email" name="email" class="form-control" autocomplete="off">
                </div>
            </div>
            <input type="submit" value="Send Email" class="btn btn-primary btn-block" name="submit">
        </form>
        <?php echo isset($gen_msg) ? '<p class="text-success" align="center">'.$gen_msg.'</p>' : ''; ?> 
        <?php echo isset($email_err_msg) ? '<p class="text-danger" align="center">'.$email_err_msg.'</p>': ''; ?> 
    </div>
    <div class="col">
        <div class="col"><h5><?php echo CATCHPHRASE; ?></h5>
            <ul class="list-group">
                <li class="list-group-item border-0">SQL Injection Security</li>
                <li class="list-group-item border-0">XSS Scripting Security</li>
                <li class="list-group-item border-0">User Enumeration Security</li>
                <li class="list-group-item border-0">Validation and Sanitization</li>
                <li class="list-group-item border-0">Strong Encrypted Passwords</li>
                <li class="list-group-item border-0">Securely Stored Credentials</li>
                <li class="list-group-item border-0">Secure Session Tokens</li>
                <li class="list-group-item border-0">Security Headers</li>
                <li class="list-group-item border-0">Log Monitoring</li>               
                <li class="list-group-item border-0">Vague/User Friendly Error Messages</li>
                <li class="list-group-item border-0">Access Control Checks</li>
            </ul>
        </div>
    </div>
</div>