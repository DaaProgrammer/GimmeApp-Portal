<?php
ob_start();

// show errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
$url = $_SERVER['REQUEST_URI'];
$parameters = parse_url($url, PHP_URL_PATH);
$path = strtolower($parameters);
$path = trim($path, '/');
if($path == ''){$path = 'home';}

$redirect_logout = ['login'];
// redirect these pages if the user role is user
$redirect_admin = ['event-organisers', 'admin-events','golf-courses','chat','users'];
// redirect these pages if the admin role is admin
$redirect_users = ['events'];

//* SESSION CHECK FOR LOGGED IN USER */
if (isset($_COOKIE['jwt'])) {
  $jwt = $_COOKIE['jwt'];
  $api_endpoint = $_ENV['API_URL'].'api/v1/auth/session.php';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_endpoint);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  $headers = array();
  $headers[] = 'Content-Type: application/json';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $data = array(
    "token" => $jwt
  );
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    // clear the jwt cookie
    setcookie("jwt", "", time() - 3600, "/");
  }
  curl_close($ch);

  $response = json_decode($result, true);

  // print_r($response);
  if(isset($response['status']) && $response['status'] == 'invalid') {
    if ($path != 'login') {
      // clear the jwt cookie
      setcookie("jwt", "", time() - 3600, "/");
      header('Location: /login');
    }
  } else {
    $_SESSION['USER_ROLE'] = $response['user_role'];
    $_SESSION['USER_NAME'] = $response['name'];
    $_SESSION['USER_SURNAME'] = $response['surname'];
    $_SESSION['USER_EMAIL'] = $response['email'];
    $_SESSION['USER_CONTACT'] = $response['contact_number'];
    
    // loop through redirect_admin array and if path matches, redirect to dashboard
    foreach($redirect_admin as $apage){
      if($path == $apage && $_SESSION['USER_ROLE'] == 'event_organiser'){
          header('Location: /');
          die;
      }
    }

    // loop through redirect_users array and if path matches, redirect
    foreach($redirect_users as $upage){
      if($path == $upage && $_SESSION['USER_ROLE'] == 'admin'){
        header('Location: /');
        die;
      }
    }

    // loop through redirect_logout array and if path matches, redirect to profile
    foreach($redirect_logout as $page){
      if($path == $page){
          header('Location: /profile');
          die;
      }
    }
  }
  
} else {
  header('Location: /login');
}
ob_end_flush();
?>