<?php 
// ONLY ADMIN RIGHTS
$page_lvl = 1;
require('includes/header.php');

if(isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1){
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        require(MYSQL);

        $trimmed = array_map('trim', $_POST);

        $fn = $ln = $e = $p = FALSE;
        // VALIDATION
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
            $email_error_msg = "Please enter your email";
        }

        if($trimmed['password1'] !== $trimmed['email']){   // check if pw is different from mail, if it matches pw policy and is same as password from both fields 
                if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?_^])[A-Za-z\d#$@!%&*?_^]{8,30}$/', $trimmed['password1'])){
                    if($trimmed['password1'] == $trimmed['password2']){
                        $p = password_hash($trimmed['password1'], PASSWORD_DEFAULT);   
                    } else {
                        $pw_error_msg = 'The two passwords must match.';
                    }
                } else {
                        $pw_error_msg = 'The password must contain at least 8 characters, one number and one special character.';
                }
            } else {
                $pw_error_msg = 'Password can\'t be same as username.';
            }
        //END OF VALIDATION
        //INSERTION INTO DB
        if($fn && $ln && $e && $p){
            $q = "SELECT email from `users-lic` where email = ? LIMIT 1";
            $stmt = $dbc->stmt_init();
            $stmt->prepare($q);
            $stmt->bind_param("s", $e);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0 ){
                $e = FALSE;
                $gen_error_msg = "Email already exists.";
            } else {
                        $q = "INSERT INTO `users-lic` (email, pass, first_name, last_name, active, registration_date, last_login) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $dbc->stmt_init();
                        $stmt->prepare($q);

                        $date = date('Y-m-d H:i:s'); 
                        $default_date = '0000-00-00 00:00:00';
                        $active = 1;
                        $stmt->bind_param("ssssiss", $e, $p, $fn, $ln, $active, $date, $default_date); 
                        $stmt->execute();
                        if($stmt->affected_rows == 1){
                            $success_msg = "Done.";
                            log_to_file('Admin added a new user', $_SESSION['user_id']);        
                        } else {
                            $gen_error_msg = "An error occured during registration";
                        }
                    }
            $dbc->close();
        } // END OF INSERTION
        if (isset($gen_error_msg)){
            $msg = 'Admin Added '. $gen_error_msg;
            log_to_file($msg, $_SESSION['user_id']);
        }
    }
} else {
    header("Location: index");
}

?>

<div class="row">
    <div class="col">
        <h1 align="center">Add User</h1>
<form action="" method="post">
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
        <?php echo $email_error_msg ?? ''; ?>
    </div>
    <div class="form-group row">
        <p class="col-sm-3">Password:</p>
        <div class="col-sm-9">
            <input type="password" name="password1" class="form-control" autocomplete="off">
        </div>
        <?php echo $pw_error_msg ?? ''; ?>
    </div>
    <div class="form-group row">
        <p class="col-sm-3">Repeat Password:</p>
        <div class="col-sm-9">
            <input type="password" name="password2" class="form-control" autocomplete="off">
        </div>
    </div>

    <div align="center"><input type="submit" value="Add User" class="btn btn-primary btn-block btn-lg"></div>
    <?php echo $success_msg ?? ''; ?>
    <?php echo $gen_error_msg ?? ''; ?>
</form>
    </div> <!-- end of col -->
    <div class="col">
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel nulla animi eos illo et. Quos aliquam, impedit nostrum nesciunt earum voluptatem eaque commodi molestias officia consequuntur aut veritatis. Alias, beatae.</p>
    </div> <!-- end of col -->
</div> <!-- end of row -->