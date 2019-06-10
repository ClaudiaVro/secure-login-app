<?php 
require('includes/header.php');
if((isset($_GET['token']) && strlen($_GET['token']) == 64)){

    echo '<form action="generate_pwd" method="post">
        <div class="form-group row">
            <label for="new_pwd" class="col-sm-3">New Password</label>
            <div class="col-sm-9">
                <input type="password" name="new_pwd" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="new_pwd" class="col-sm-3">Repeat Password</label>
            <div class="col-sm-9">
                <input type="password" name="rpt_pwd" class="form-control">
            </div>
        </div>
        <input type="submit" value="Set New Password" name="submit" class="btn btn-primary btn-block">
        <input type="hidden" name="token" value="'. $_GET['token'].'">
    </form>';

} else if($_SERVER['REQUEST_METHOD'] == 'POST'){
        require(MYSQL);
        // SANITIZATION / VALIDATION
        $trimmed = array_map('trim', $_POST);
        if(!(empty($trimmed['new_pwd']) || empty($trimmed['rpt_pwd']))){
            if((preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?_^])[A-Za-z\d#$@!%&*?_^]{8,30}$/', $trimmed['new_pwd']))){
                if($trimmed['new_pwd'] == $trimmed['rpt_pwd']){
                    $token = $trimmed['token'];
                    $q = "SELECT email, pass, pw_reset_timer from `users-lic` where pw_reset_token = '$token' LIMIT 1"; 
                    $result = $dbc->query($q);         
                    if($result->num_rows == 1){
                        $row = $result->fetch_assoc();
                        if($row['pw_reset_timer'] > date('U')){ // time left on pw reset
                            if($row['email'] != $trimmed['new_pwd'] && !(password_verify($trimmed['new_pwd'], $row['pass']))){ // passwords are different from db email and pass
                                $q = "UPDATE `users-lic` SET pass = ?, pw_reset_token = ?, pw_reset_timer = ? where email = ?";
                                $stmt = $dbc->stmt_init();
                                $stmt->prepare($q);

                                $email = $row['email'];
                                $p = password_hash($trimmed['new_pwd'], PASSWORD_DEFAULT);
                                $default_value = NULL;

                                $stmt->bind_param("siis", $p, $default_value, $default_value, $email);
                                $stmt->execute();
                                if($stmt->affected_rows ==  1){
                                    $gen_msg = 'Password has been reset. You will be redirected to the main page in 3 seconds.';
                                    log_to_file($gen_msg);    
                                    header("refresh: 3; index");
                            } else {
                                $gen_err_msg =  'An error has occured.';
                            }
                        } else {
                            $gen_err_msg = 'Too similar to other credentials. Please try another password';
                        }
                    } else {
                        $gen_err_msg = 'Link expired!';
                    }
                } else {
                    $pw_err_msg =  'Passwords must be similar';
                }
            } else {
                $pw_err_msg = 'Password does not meet password policy';
            }
        } else {
            $pw_err_msg = 'Input all fields!';
        }
    } else {
        $gen_err_msg = 'Error! Invalid link, check your link again.';
        header("refresh: 3; index");
    }
}
if(isset($gen_err_msg)){
    echo '<p class="text-danger" align="center">'.$gen_err_msg.'</p>';
    log_to_file($gen_err_msg);
} elseif(isset($gen_msg)){
    echo '<p class="text-success" align="center">'.$gen_msg.'</p>';
    log_to_file($gen_msg);
}


