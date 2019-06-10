<?php 
// Only admin rights
$page_lvl = 1;
require('includes/header.php');
require(MYSQL);

if(!isset($_SESSION['user_token'])){
    $_SESSION['user_token'] = generate_token();
}


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])){
    if($_SESSION['user_token'] == $_POST['user_token']){
        $email = $_POST['email'];
        $q = "DELETE from `users-lic` WHERE email = '$email' and user_level = 0";
        if($dbc->query($q)){
            echo "Deleted";
            destroy_token();
        }
    } else {
        echo 'CSRF attack detected';
    }
}

$q= "SELECT email, registration_date, active from `users-lic` where user_level = 0";
$result = $dbc->query($q);
$table_contents = '';
if($result){
    while($row = $result->fetch_assoc()){
    $table_contents .= '<tr>
        <td><form action="view_users" method="post">
          <input type="hidden" name= "email" value="'. $row['email'].'">
          <input type="submit" value="Delete" name="submit" class="btn btn-link">
          <input type="hidden" name="user_token" value="'.$_SESSION['user_token'].'">
        </form></td>
        <td>'. $row['email'] .'</td>
        <td>'.$row['registration_date'].'</td>
        <td>'.$row['active'].'</td>
    </tr>';
    }
}

$dbc->close();
echo '
<h1>Admin Panel</h1>
<a href="add_user">Add User</a>
<table class="table table-bordered">
       <tr>
         <th><strong>Delete</strong></th>
         <th><strong>Username</strong></th>
         <th><strong>Registration Date</strong></th>
         <th><strong>Active</strong></th>
       </tr>' .$table_contents .
'</table>';
