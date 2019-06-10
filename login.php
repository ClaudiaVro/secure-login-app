<?php
require('includes/header.php');

if(isset($_SESSION['user_id'])){
    header("Location:index");
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['submit'])){
        $e = $pass = FALSE;

        $trimmed = array_map('trim', $_POST);
        if(!empty($trimmed['email'])){
            if(filter_var($trimmed['email'], FILTER_VALIDATE_EMAIL)){
                $e = $trimmed['email'];
            } else {
                $email_err_msg = '<p class="text-danger" align="center">The email submitted is not valid.</p>';
            } 
        } else {
            $email_err_msg = '<p class="text-danger" align="center">Please input an email!</p>';
        }

        if(!empty($trimmed['pass'])){
                $pass = $trimmed['pass'];
            } else {
                $pass_err_msg = '<p class="text-danger" align="center">Please input a password!</p>';
            } 
    } else {
            header("Location: index");
            exit();
    }


    require(MYSQL);

    if($e && $pass){
        sleep(rand(1,3));
        $q = "SELECT user_id, email, pass, user_level, failed_logins, lockout, lockout_time from `users-lic` where email = ? and active = 1 LIMIT 1";  
        $stmt = $dbc->stmt_init();
        $stmt->prepare($q);
        $stmt->bind_param("s", $e);
        $stmt->execute();        
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $row = $result->fetch_assoc(); 
            $failed_logins = $row['failed_logins'];
            if($row['lockout'] == 1 && (CURRENT_DATE - $row['lockout_time'] < 600)){              
                    $gen_err_msg =  'Your account is currently locked. Please try later.';
            } else {
                $pwdCheck = password_verify($pass, $row['pass']);
                if($pwdCheck  == true){
                    if($row['lockout'] == 1){
                        if(CURRENT_DATE - $row['lockout_time'] > 600){
                            $q = "UPDATE `users-lic` set lockout = 0, lockout_time = 0, failed_logins = 0 where email = ?";
                            $stmt1 = $dbc->stmt_init();
                            $stmt1->prepare($q);
                            $stmt1->bind_param("s", $e);
                            $stmt1->execute();        
                            // if(!$stmt->affected_rows == 1){
                            //     echo 'An error has occured during lockout managaement';
                            // }
                            $stmt1->close();
                        }  
                    } 
                    session_regenerate_id(true);                   
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['user_level'] = $row['user_level'];
                    $_SESSION['last_activity'] = time();
                    
                    $failed_logins = 0;
                    log_to_file("SUCCESS", $_SESSION['user_id']);
                    header("Location: index");
                    
                } else {
                    $failed_logins++;
                    $gen_err_msg = 'Username or password is incorrect. Please try again.';
                }
                $q = "UPDATE `users-lic` set failed_logins = {$failed_logins} where email = ?";
                $stmt1 = $dbc->stmt_init();
                $stmt1->prepare($q);
                $stmt1->bind_param("s", $e);
                $stmt1->execute();        
                if(!$stmt1->affected_rows == 1){
                    echo 'An error has occured during upate of failed logins counter';
                }
                $stmt1->close();              
                if($failed_logins > 2){
                    $curr_date = CURRENT_DATE;
                    $q= "UPDATE `users-lic` SET lockout = 1, lockout_time = $curr_date where email = ?";
                    $stmt2 = $dbc->stmt_init();
                    $stmt2->prepare($q);
                    $stmt2->bind_param("s", $e);
                    $stmt2->execute();        
                    $stmt2->close();
                    $gen_err_msg =  'Your account is currently locked. Please try later.';
                }            
            }
            
        } else {
            $gen_err_msg = 'Username and/or password is incorrect. Please try again.';
        }
    }
    if(isset($gen_err_msg)){
        log_to_file($gen_err_msg);
    }
}
?>


<div class="row">
    <div class="col">
        <h1 align="center">Login</h1>
        <form action="" method="post">
            <div class="form-group row">
                <label for="email" class="col-sm-3">Email</label>
                <div class="col-sm-9">
                    <input type="email" name="email" class="form-control" value="<?php echo isset($trimmed['email']) ? htmlspecialchars($trimmed['email']): ''; ?>" autocomplete="off">
                </div>
            </div>
            <?php echo $email_err_msg ?? ''; ?>   

            <div class="form-group row">
                <label for="pass" class="col-sm-3">Password</label>
                <div class="col-sm-9">
                    <input type="password" name="pass" class="form-control" autocomplete="off"> 
                </div>
            </div>
             <?php echo $pass_err_msg ?? ''; ?>   
            <div align="center"><input type="submit" value="Submit" name ="submit" class="btn btn-primary btn-lg btn-block"></div>
             <?php echo isset($gen_err_msg) ? '<p class="text-danger" align="center"> '. $gen_err_msg . '</p>': ''; ?>   
        </form>
    </div> <!-- end of first column -->
    <div class="col"><h5><?php echo CATCHPHRASE; ?></h5>
            <ul class="list-group">
                <li class="list-group-item border-0">SQL Injection Security</li>
                <li class="list-group-item border-0">XSS Scripting Security</li>
                <li class="list-group-item border-0">Brute Forcing Security</li>
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
</div> <!-- end of row -->