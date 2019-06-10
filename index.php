<?php
require('includes/header.php');


if(isset($_SESSION['booted'])){
    echo $_SESSION['booted'];
    unset($_SESSION['booted']);
}
?>
<div class="row">
    <div class="col">
    <h1 align="center">Entry Page</h1>
        <?php 
        if(isset($_SESSION['user_token'])){
            destroy_token();
        }

        if(isset($_SESSION['user_id'])){
            echo '<p class="text-success">Welcome to the site!</p>';
        } else {
            echo '<p>Welcome! To use the login functions, browse the navbar. </p>';
        }  
        
?>
    </div>
    <div class="col">
    <h5><?php echo CATCHPHRASE; ?></h5>
    <ul class="list-group">
        <li class="list-group-item">Log Monitoring</li>
        <li class="list-group-item">Security Headers</li>
        
    </ul>
</div>

<?php
include('includes/footer.php');

 