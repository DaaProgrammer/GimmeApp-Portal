<?php 
    session_start();
    // Delete the cookie called jwt
    setcookie("jwt", "", time() - 3600, "/");
    header('Location: /login');
    $_SESSION = array();
?>