<?php 

require_once('includes/header.php');
// echo $_SESSION['user_token'] . ' '.$_POST['user_token'] . '<br>';


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // check anti CSRF
    $token = $_POST['user_token'];
    if(!(check_token($token))){
        $gen_err_msg = 'CSRF attack detected.';
    } else {
        require(MYSQL);
        // SANITIZATION / VALIDATION
        $trimmed = array_map('trim', $_POST);
        if(!(empty($_POST['curr_pwd']) || empty($_POST['new_pwd']) || empty($_POST['rpt_pwd']))){
            if((preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?_^])[A-Za-z\d#$@!%&*?_^]{8,30}$/', $trimmed['curr_pwd'])) && (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?_^])[A-Za-z\d#$@!%&*?_^]{8,30}$/', $trimmed['new_pwd']))){
                if($trimmed['new_pwd'] == $trimmed['rpt_pwd']){
                    $q = "SELECT pass, email, failed_logins, lockout, lockout_time from `users-lic` where user_id = {$_SESSION['user_id']} LIMIT 1"; 
                    $result = $dbc->query($q);
                    if($result->num_rows == 1){
                        $row = $result->fetch_assoc();
                        $failed_logins = $row['failed_logins'];
                        if($row['lockout'] == 1){
                            session_unset();
                            destroy_token();
                            session_destroy();
                            header("Location: index");
                        } else {
                            $pwd_check = password_verify($trimmed['curr_pwd'], $row['pass']);
                            sleep(rand(1,2));
                            if($pwd_check  == true){
                            $secondary_pwd_check = password_verify($trimmed['new_pwd'], $row['pass']);
                                if($row['email'] != $trimmed['curr_pwd'] && $secondary_pwd_check == 0){
                                    $new_p = password_hash($trimmed['new_pwd'], PASSWORD_DEFAULT);
                                    $q = "UPDATE `users-lic` set pass = ? where user_id = {$_SESSION['user_id']}";
                                    $stmt = $dbc->stmt_init();
                                    $stmt->prepare($q);
                                    $stmt->bind_param("s", $new_p);
                                    $stmt->execute();
                                    if($stmt->affected_rows == 1){
                                        $gen_success_msg = 'The new password has been set.';
                                        log_to_file($gen_success_msg, $_SESSION['user_id']);
                                        destroy_token();
                                        header("refresh: 3; index");
                                    }
                                } else {
                                    $gen_err_msg = 'Password is too similar to email/previous credentials. Please try another password';
                                }                    
                            } else {
                                $failed_logins++;
                                $gen_err_msg = 'One of the credential inputs is wrong.';
                                $q = "UPDATE `users-lic` set failed_logins = {$failed_logins} where user_id = {$_SESSION['user_id']} LIMIT 1";
                                $dbc->query($q);
                                // if(!$dbc->affected_rows == 1){
                                //     $gen_err_msg =  'An error has occured during upate of failed logins counter';
                                // }
                                if($failed_logins > 2){
                                    $curr_date = CURRENT_DATE;
                                    $q= "UPDATE `users-lic` SET lockout = 1, lockout_time = $curr_date where user_id = {$_SESSION['user_id']} LIMIT 1";
                                    $dbc->query($q);   
                                    // if(!$dbc->affected_rows == 1){
                                    //     $gen_err_msg = 'An error has occured during lockout after 3 tries';
                                    // }
                                }  
                            }
                        }   
                    } else {
                        $gen_err_msg =  "Not a valid email.";
                    }
                } else {
                    $pw_err_msg =  "The passwords must match!";
                }
            } else {
                $pw_err_msg =  "One of the passwords input do not adhere to the password policy.";
            } 
        } else {
            $pw_err_msg =  "Please input all fields.";
        }                
    }
    if(isset($gen_err_msg)){
        log_to_file($gen_err_msg, $_SESSION['user_id']);
        destroy_token(); 
    }  
}

if(isset($_SESSION['user_id'])){
    $_SESSION['user_token'] = generate_token();  
} else {
    header("Location: index");
}

?>



<div class="row">
    <div class="col">
        <form action="" method="post">
        <div class="form-group row">
            <label for="curr_pwd" class="col-sm-3">Current Password</label>
            <div class="col-sm-9">
                <input type="password" name="curr_pwd" class="form-control" autocomplete="off">
            </div>
        </div>
        <div class="form-group row" >
            <label for="new_pwd" class="col-sm-3">New Password</label>
            <div class="col-sm-9">
                <input type="password" name="new_pwd" class="form-control" autocomplete="off"> 
            </div>
        </div>
        <div class="form-group row">
            <label for="rpt_pwd" class="col-sm-3">Repeat Password</label>
            <div class="col-sm-9">
                <input type="password" name="rpt_pwd" class="form-control" autocomplete="off"> 
            </div>
        </div>
        <input type="submit" value="Change password" name="submit" class="btn btn-primary btn-block">
        <input type="hidden" name="user_token" value="<?php echo $_SESSION['user_token']; ?>">
    </form>
    <?php echo isset($gen_err_msg) ? '<p align="center" class="text-danger">'. $gen_err_msg .'</p>' : ''; ?>
    <?php echo isset($pw_err_msg) ? '<p align="center" class="text-danger">'. $pw_err_msg .'</p>' : ''; ?>
    <?php echo isset($gen_success_msg) ? '<p align="center" class="text-success">'. $gen_success_msg .'</p>' : ''; ?>
    </div>
        <div class="col"><h5><?php echo CATCHPHRASE; ?></h5>
            <ul class="list-group">
                <li class="list-group-item border-0">SQL Injection Security</li>
                <li class="list-group-item border-0">XSS Scripting Security</li>
                <li class="list-group-item border-0">Brute Forcing Security</li>
                <li class="list-group-item border-0">CSRF Token Security</li>
                <li class="list-group-item border-0">User Enumeration Security</li>
                <li class="list-group-item border-0">Validation and Sanitization</li>
                <li class="list-group-item border-0">Strong Encrypted Passwords</li>
                <li class="list-group-item border-0">Securely Stored Credentials</li>
                <li class="list-group-item border-0">Secure Session Tokens</li>
                <li class="list-group-item border-0">Security Headers</li>
                <li class="list-group-item border-0">Log Monitoring</li>               
                <li class="list-group-item border-0">Vague/User Friendly Error Messages</li>
                <li class="list-group-item border-0">Access Control Checks</li>
            </ul></div>
</div>

<?php

include('includes/footer.php');