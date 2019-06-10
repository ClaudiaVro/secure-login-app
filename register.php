<?php

include('includes/header.php');

if(isset($_SESSION['user_id'])){
    header("Location:index");
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    require(MYSQL);
    $trimmed = array_map('trim', $_POST);
    $fn = $ln = $e = $p = FALSE;
    if(empty($trimmed['first_name']) || empty($trimmed['last_name']) || empty($trimmed['email']) || empty($trimmed['password1']) || empty($trimmed['password2'])){
        $gen_error_msg = "One or more fields are empty. Please complete all fields";
    } else {
          
        if(preg_match('/^[A-Z\'.-]{2,20}$/i', $trimmed['first_name'])){
            $fn = $trimmed['first_name'];
        } else {
            $fn_error_msg = "Please enter your first name";
        }
        if(preg_match('/^[A-Z\'.-]{2,40}$/i', $trimmed['last_name'])){
            $ln = $trimmed['last_name'];
        } else {
            $ln_error_msg = "Please enter your last name";
        }

        if(filter_var($trimmed['email'], FILTER_VALIDATE_EMAIL)) {
            $e = $trimmed['email'];
        } else {
            $email_error_msg = 'Invalid email';
        }

        if($trimmed['password1'] !== $trimmed['email']){    
            if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?_^])[A-Za-z\d#$@!%&*?_^]{8,30}$/', $trimmed['password1'])){
                if($trimmed['password1'] == $trimmed['password2']){
                    $p = password_hash($trimmed['password1'], PASSWORD_DEFAULT);   
                } else {
                    $pw_error_msg = 'The two passwords must match.';
                }
            } else {
                    $pw_error_msg =  'The password must contain at least 8 characters, one number and one special character.';
            }
        } else {
            $pw_error_msg = 'Password can\'t be same as username';
        }
    }
    
        
    if($fn && $ln && $e && $p){
        // check for existing email
            $q = "SELECT email from `users-lic` where email = ? LIMIT 1";
            $stmt = $dbc->stmt_init();
            $stmt->prepare($q);
            $stmt->bind_param("s", $e);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0 ){
                $e = FALSE;
                // bogus message
                    $success_msg  = 'Thank you for registering. Check your email for an activation link.';
                    header("refresh: 3; index");
            } else { // insert data into db, prepare email to send
                $a = md5(uniqid(random_int(1,10000000), true));
                $q = "INSERT INTO `users-lic` (email, pass, first_name, last_name, active, registration_date, register_token, last_login) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $dbc->stmt_init();
                $stmt->prepare($q);

                $date = date('Y-m-d H:i:s'); 
                $default_date = '0000-00-00 00:00:00';
                $active = 0;
                $stmt->bind_param("ssssisss", $e, $p, $fn, $ln, $active, $date, $a, $default_date); 
                $stmt->execute();
                if($stmt->affected_rows == 1){
                    require_once('includes/send_email.php');
                    $body = "Thank you for registering. To activate your account, please open this link:\n\n";
                    $body .= '<strong>https://' . BASE_URL . 'activate?&y='. $a .'</strong>';
                    if(send_email($e, $body, $fn)){
                        $success_msg = 'Thank you for registering. Check your email for an activation link.';
                        //header("refresh: 3; index");
                    } else {
                        $gen_error_msg = 'The email could not be sent. Please try again later.';
                    }                
                } else {
                    $gen_error_msg = 'An error occured during registration';
                }
            }
        $dbc->close();
        }
}

?>
<div class="row">
    <div class="col">
        <h1 align="center">Registration</h1>
<form action="register" method="post">
    <div class="form-group row">
        <p class="col-sm-3">First Name:</p>
        <div class="col-sm-9">
            <input type="text" name="first_name" class="form-control" value ="<?php echo isset($trimmed['first_name']) ? htmlspecialchars($trimmed['first_name']): ''; ?>" autocomplete="off">
        </div>      
    </div>
    <div class="form-group row">
        <p class="col-sm-3">Last Name:</p>
        <div class="col-sm-9">
            <input type="text" name="last_name" class="form-control" value ="<?php echo isset($trimmed['last_name']) ? htmlspecialchars($trimmed['last_name']): ''; ?>" autocomplete="off">
        </div>
        
    </div>
    <div class="form-group row">
        <p class="col-sm-3">Email:</p>
        <div class="col-sm-9">
            <input type="email" name="email" class="form-control" value ="<?php echo isset($trimmed['email']) ? htmlspecialchars($trimmed['email']): ''; ?>" autocomplete="off">
        </div>
        <?php echo isset($email_error_msg) ?  '<p class="text-danger">'.$email_error_msg.'</p>': ''; ?>
    </div>
    <div class="form-group row">
        <p class="col-sm-3">Password:</p>
        <div class="col-sm-9">
            <input type="password" name="password1" class="form-control" autocomplete="off">
        </div>
        <?php echo isset($pw_error_msg) ? '<p class="text-danger">'.$pw_error_msg.'</p>': ''; ?>
    </div>
    <div class="form-group row">
        <p class="col-sm-3">Repeat Password:</p>
        <div class="col-sm-9">
            <input type="password" name="password2" class="form-control" autocomplete="off">
        </div>
    </div>

    <div align="center"><input type="submit" value="Register" class="btn btn-primary btn-block btn-lg"></div>
    <?php echo isset($gen_error_msg) ? '<p class="text-danger" align="center">'.$gen_error_msg.'</p>': '';
    echo isset($success_msg) ?  '<p class="text-success" align="center">'.$success_msg.'</p>': '' ?>

</form>
    </div> <!-- end of col -->
    <div class="col">
            <h5><?php echo CATCHPHRASE; ?></h5>
            <ul class="list-group">
                <li class="list-group-item border-0">SQL Injection Security</li>
                <li class="list-group-item border-0">XSS Scripting Security</li>
                <li class="list-group-item border-0">User Enumeration Security</li>
                <li class="list-group-item border-0">Validation and Sanitization</li>
                <li class="list-group-item border-0">Strong Encrypted Passwords</li>
                <li class="list-group-item border-0">Securely Stored Credentials</li>
                <li class="list-group-item border-0">Security Headers</li>
                <li class="list-group-item border-0">Log Monitoring</li>
                <li class="list-group-item border-0">Vague/User Friendly Error Messages</li>
                <li class="list-group-item border-0">Access Control Checks</li>
            </ul>
    </div> <!-- end of col -->
</div> <!-- end of row -->

<?php include('includes/footer.php');
