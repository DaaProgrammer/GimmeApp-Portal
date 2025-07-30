<?php 
$pageTitle = "Events";
require_once 'session/session.php';
// require_once 'session/permission_handler.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if(!isset($_POST['event_id'])){
    echo <<<EOD
    <h2>Error 01:</h2>
    <p>Sorry, we could not find the event that you want to update. Please try again later or contact your administrator for support.</p><br/>
    <p>You will be redirected soon. Please wait...</p>
    <script>
        window.location.href = "/events/";
    </script>
    EOD;
    exit;
}

// cURL request to get event data
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $_ENV['API_URL'].'api/v1/event/admin_get_event.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "token":"'.$_COOKIE['jwt'].'",
    "event_id":"'.$_POST['event_id'].'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$eventResponse = json_decode(curl_exec($curl));

curl_close($curl);

if($eventResponse->msg != "Success") {
    // echo $eventResponse->current_uid != $currentEvent->uid && $_SESSION['USER_ROLE'] == "event_organiser";
    echo json_encode($eventResponse);
    echo <<<EOD
    <h2>Error 02:</h2>
    <p>Sorry, we could not find the event that you want to update. Please try again later or contact your administrator for support.</p><br/>
    <p>You will be redirected soon. Please wait...</p>
    <script>
        window.location.href = "/events/";
    </script>
    EOD;
    exit;
}

$currentEvent = $eventResponse->data;

// Check if user is authorised to edit this event
if($eventResponse->current_uid != $currentEvent->uid && $_SESSION['USER_ROLE'] == "event_organiser") {
    // echo $eventResponse->current_uid != $currentEvent->uid && $_SESSION['USER_ROLE'] == "event_organiser";
    // echo json_encode($eventResponse);
    echo <<<EOD
    <h2>Error 03:</h2>
    <p>Sorry, you are not authorised to edit this event. Please try again later or contact your administrator for support.</p><br/>
    <p>You will be redirected soon. Please wait...</p>
    <script>
        window.location.href = "/events/";
    </script>
    EOD;
    exit;
}

// Get event invitations
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $_ENV['API_URL'].'api/v1/invitations/get_event_invitations.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "token":"'.$_COOKIE['jwt'].'",
    "event_id":"'.$_POST['event_id'].'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$invitationResponse = json_decode(curl_exec($curl));

curl_close($curl);

$invitationList = $invitationResponse->data;

// Get event scores
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $_ENV['API_URL'].'api/v1/scores/get_event_scores.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "token":"'.$_COOKIE['jwt'].'",
    "event_id":"'.$_POST['event_id'].'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$scoresResponse = json_decode(curl_exec($curl));
if($scoresResponse->msg == "Event not found"){
    $scoresList = [];
}else{
    $scoresList = $scoresResponse->data;
}

curl_close($curl);

// Get event course
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $_ENV['API_URL'].'api/v1/courses/get_course.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "token":"'.$_COOKIE['jwt'].'",
    "course_id":"'.$currentEvent->event_course.'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$courseResponse = json_decode(curl_exec($curl));
if($courseResponse->msg != "success"){
    $eventCourse = [];
    $teeData = [];
}else{
    $eventCourse = $courseResponse->data[0];
    $teeData = $courseResponse->data[0]->tee_data;
}
curl_close($curl);
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>

<script>
    const API_URL = "<?php echo $_ENV['API_URL']; ?>";
    const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";  
</script>
<script src="../util/util.js" type="module"></script>
<body class="g-sidenav-show bg-gray-200">

<?php include 'partials/left-nav.php'; ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<style>
.border-radius-xl {
    border-radius: 0.15rem;
}
.icon-lg {
    width: 45px;
    height: 45px;
}    
.loader {
    display: inline-block;
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 3px solid #3498db;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.sidenav {
  z-index: 1038;
}

.actions{
    margin-top: 20px;
    display:flex;
    justify-content:left;
    align-items:center;
}
.form{
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.form-input{
    width: 100%;
    display: flex;
    flex-direction: column;
}
.form-input input{
    width: 40%;
}
.form-save{
    margin-top:10px
}
.modal{
    background-color: #000000b8;
}
.scorecard-label-helper{
    background-color: #00b240;
    color: white;
    text-align:center;
}

#edit-scorecard-modal--table > thead > tr > td.scorecard-label-helper{
    text-align:center;
}

#scoring-modal-settings--custom-settings{
    display:none
}

#scoreboard-row{
    background-color: #f0f0f0;
}
.scorecard-input{
    width:35px
}

/* Custom settings */
#scoring-modal-settings--custom-settings--container{
    display:flex;
    flex-direction:column;
    gap:10px;
}
.custom-settings--hole{
    background-color: #ebebeb;
    padding: 20px;
}
.pin-gps-input-container{
    display: inline-block;
    white-space:nowrap;
}
#team-members-list{
    background-color:#f3f3f3;
    padding: 25px
}
#team-members-list > li{
    margin-bottom:10px;
    font-weight:bold
}
</style>    
    <!-- Navbar -->
        <?php include 'partials/top-nav.php'; ?>
    <!-- End Navbar -->

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
              <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3">Update Event</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">

                    <!-- Render Tabs -->
                    <div id="tabs">

                    </div>

                    <!-- Render content -->
                    <div class="tab-content">
                        <!-- Tab views -->
                        <?php include './views/update-event-general.php'; ?>
                        <?php include './views/update-event-portal.php'; ?>

                        <!-- GOLFERS -->
                        <?php include './views/update-event-golfers.php'; ?>

                        <!-- END GOLFERS -->

                        <?php include './views/update-event-scoring.php'; ?>
                        <!-- End tab views -->
                    </div>
                </div>
              </div> 
            </div>
        </div>  
        <?php include 'partials/footer.php'; ?>
    </div>
</main>

<script type="module">

// Tab settings
new w2tabs({
    box: '#tabs',
    name: 'tabs',
    active: 'general',
    tabs: [
        { id: 'general', text: 'General' },
        { id: 'portal', text: 'Portal' },
        { id: 'golfers', text: 'Golfers' },
        { id: 'scoring', text: 'Scoring' },
    ],
    onClick(event) {
        query('#selected-tab').html(event.target);
        console.log(event.target)
        switch (event.target) {
            case 'general':
                $("#settings-general").show(500);
                $("#settings-portal").hide();
                $("#settings-golfers").hide();
                $("#settings-scoring").hide();
            break;
            case 'portal':
                $("#settings-general").hide();
                $("#settings-portal").show(500);
                $("#settings-golfers").hide();
                $("#settings-scoring").hide();

                setTimeout(function() {
                    // --------------------------------------------------------------------------------------------------------------------
                    // Instantiate W2UI elements here
                    // window.instantiateBadgeSelector;
                    // window.instantiateBannerSelector;
                    window.instantiateColorPicker;
                }, 510);

            break;
            case 'golfers':
                $("#settings-general").hide();
                $("#settings-portal").hide();
                $("#settings-golfers").show(500);
                $("#settings-scoring").hide();

                try{
                    setTimeout(function() {
                        window.initializeGolfersTable();
                        window.populateGolfersTable();
                        window.grid.refresh();
                    }, 510);
                }catch(err){
                    // console.log(err)
                }
            break;
            case 'scoring':
                $("#settings-general").hide();
                $("#settings-portal").hide();
                $("#settings-golfers").hide();
                $("#settings-scoring").show(500);

                try{
                    setTimeout(function() {
                        window.initializeScoringTable();
                        window.populateScoringTable();
                        window.scoring_grid.refresh();
                    }, 510);
                }catch(err){
                    // console.log(err)
                }
            break;
        
            default:
                break;
        }
    }
}) 

document.addEventListener("DOMContentLoaded", function() {

<?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
    let startEventBtn = document.getElementById('start-game-btn');
    let completeEventBtn = document.getElementById('complete-game-btn');

    <?php
        if($currentEvent->event_status == "pending"):
    ?>
    startEventBtn.addEventListener('click', function(e) {
        let data = {
            token:TOKEN,
            event_id:<?= $currentEvent->id; ?>
        };

        $.ajax({
            url: API_URL + 'api/v1/event/start_event.php',
            method: 'POST',
            data: JSON.stringify(data),
            headers: {
                "Content-Type": "application/json"
            },
            success: function(bannerResponse) {
                console.log(bannerResponse);
                gimmeToast("Event started",'success');
                window.location.reload();
            },
            error: function(error) {
                console.error('Error updating event');
                gimmeToast("Could not update event",'error');
                window.imagesSaved = {error:error};
            }
        });
    });
    <?php
    elseif ($currentEvent->event_status == "active"):
    ?>
    completeEventBtn.addEventListener('click', function(e) {

        let data = {
            token:TOKEN,
            event_id:<?= $currentEvent->id; ?>
        };

        $.ajax({
            url: API_URL + 'api/v1/event/complete_event.php',
            method: 'POST',
            data: JSON.stringify(data),
            headers: {
                "Content-Type": "application/json"
            },
            success: function(bannerResponse) {
                console.log(bannerResponse);
                gimmeToast("Event completed",'success');
                window.location.reload();
            },
            error: function(error) {
                console.error('Error updating event');
                gimmeToast("Could not update event",'error');
                window.imagesSaved = {error:error};
            }
        });
    });
<?php
    endif;
endif;
?>

});

</script>
</body>
</html>
