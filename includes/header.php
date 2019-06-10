<?php 
   session_start();
   require_once('includes/config.inc.php');
   $register_link ='<a class="nav-link" href="register">Register<span class="sr-only">(current)</span></a>';
   $login_link = '<a class="nav-link" href="login">Login<span class="sr-only">(current)</span></a>';
   $logout_link = '<form action="logout" method="POST">
  <input type="submit" value="Logout" name="log_out" class="nav-link btn btn-link">
  </form>';
  $change_pwd_link = '<a class="nav-link" href="change_pwd">Change password<span class="sr-only">(current)</span></a>';
  $lost_pwd_link = '<a class="nav-link" href="lost_pwd">Lost password<span class="sr-only">(current)</span></a>';
  
  if(isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1){
    $view_users = '<a class="nav-link" href="view_users">View Users(admin only)<span class="sr-only">(current)</span></a>';
  }
  
  header("Content-Security-Policy: script-src 'self' https://code.jquery.com/jquery-3.3.1.slim.min.js");
  header("Referrer-Policy: same-origin");
  header("X-Frame-Options: deny");
  header("X-XSS-Protection: 1; mode=block");
  header("X-Content-Type-Options: nosniff");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Secure Login</title>
    <link rel="stylesheet" href="includes/bootstrap.css">
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
  <a class="navbar-brand" href="index">Secure Login</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <?php echo isset($_SESSION['user_id']) ? $change_pwd_link : $register_link; ?>
      </li>
      <li class="nav-item">
        <?php echo isset($_SESSION['user_id']) ? $logout_link : $login_link; ?>
      </li>
      <li class="nav-item">
        <?php echo isset($_SESSION['user_id']) ? '' : $lost_pwd_link; ?>
      </li>
      <li class="nav-item">
        <?php echo $view_users ?? ''; ?>
      </li>
    </ul>
  </div>
</nav>
<main>
<div class="container" style="margin-top:3%">
