<?php 
$pageTitle = "Dashboard";
require_once 'session/session.php';

$curl = curl_init();
if($_SESSION['USER_ROLE'] == "event_organiser"){
  $post_fields = '{
    "token":"'.$_COOKIE['jwt'].'",
    "is_event_organiser":"true"
  }';
}else{
  $post_fields = '{
    "token":"'.$_COOKIE['jwt'].'",
    "is_event_organiser":"false"
  }';
}
curl_setopt_array($curl, array(
  CURLOPT_URL => $_ENV['API_URL'].'api/v1/util/get_admin_statistics.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $post_fields,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$statsResponse = json_decode(curl_exec($curl));

curl_close($curl);

if($statsResponse->msg != "Success") {
  http_response_code(400);
  echo json_encode(['msg'=>'Error 01: ']);
  exit;
}

$statsData = $statsResponse->statistics;

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>

<body class="g-sidenav-show bg-gray-200">
<?php include 'partials/left-nav.php'; ?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <?php include 'partials/top-nav.php'; ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">

            <?php if($_SESSION['USER_ROLE'] != "event_organiser"):?>
              <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">people</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Total Users</p>
                <h4 class="mb-0"><?= $statsData->user ?></h4>
              </div>
            <?php elseif($_SESSION['USER_ROLE']  == "event_organiser"):?>
              <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">people</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Total Events</p>
                <h4 class="mb-0"><?= $statsData->eventCount ?></h4>
              </div>
            <?php endif;?>

            </div>
          </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
            <?php if($_SESSION['USER_ROLE'] != "event_organiser"):?>
              <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">event</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Total Events</p>
                <h4 class="mb-0"><?= $statsData->event ?></h4>
              </div>
            <?php elseif($_SESSION['USER_ROLE']  == "event_organiser"):?>
              <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">event</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Events Completed</p>
                <h4 class="mb-0"><?= $statsData->eventsCompleted ?></h4>
              </div>
            <?php endif;?>
            </div>
          </div>
        </div>
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
            <?php if($_SESSION['USER_ROLE'] != "event_organiser"):?>
              <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">golf_course</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Games Played</p>
                <h4 class="mb-0"><?= $statsData->match ?></h4>
              </div>
            <?php elseif($_SESSION['USER_ROLE']  == "event_organiser"):?>
              <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">golf_course</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Event Invitations</p>
                <h4 class="mb-0"><?= $statsData->eventInvitations ?></h4>
              </div>
            <?php endif;?>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
      </div>

    <?php include 'partials/footer.php'; ?>

  </div>
</main>
</body>
</html>