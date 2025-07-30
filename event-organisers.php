<?php 
$pageTitle = "Event Organisers";
require_once 'session/session.php';
// require_once 'session/permission_handler.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>

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
                    <h6 class="text-white text-capitalize ps-3">Users</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    <div class="card-header pb-3 px-3">
                    <p class="mb-0">Add new event organisers.</p>
                    <p style="font-size:12px;color:#000" class="mb-0"><em>If the selected email address already exists as a user then you can convert the user account to an event organiser account under the users tab.</em></p>
                    </div>


                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="courses" class="w-100 px-6">
                        <div style="width: 100%; max-width: 1000px; margin-top: 30px; min-height: 100px;" class="w2ui-reset w2ui-form">
                            <div class="card card-body mx-3 mx-md-4 mt-2 mb-3">
                                <div class="h-100">
                                <h5 class="mb-1">
                                    New Event Organiser
                                </h5>
                                </div>
                                    <div class="card-header pb-0 p-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="w2ui-field">
                                                    <label>Name:</label>
                                                    <div><input id="eventu-name" style="width: 300px" type="text"></div>
                                                </div>
                                                <div class="w2ui-field">
                                                    <label>Surname:</label>
                                                    <div><input id="eventu-surname" style="width: 300px" type="text"></div>
                                                </div>
                                                <div class="w2ui-field">
                                                    <label>Email:</label>
                                                    <div><input id="eventu-email" style="width: 300px" type="text"></div>
                                                </div>
                                                <div class="w2ui-field">
                                                    <label>Contact Number:</label>
                                                    <div><input id="eventu-contact" style="width: 300px" type="text"></div>
                                                </div>

                                                <div class="w2ui-field w2ui-span6" style="">
                                                    <label>Welcome Email</label>
                                                    <div>
                                                        <input id="eventu-confirm" name="single.toggle" class="w2ui-input w2ui-toggle  " type="checkbox" tabindex="14">
                                                    <div>
                                                    <div>
                                                    </div></div></div>
                                                </div>

                                                <p style="font-size:12px;color:#000" class="mb-0"><em>A default password will be generated and sent via email to the event orgaiser in their welcome email.</em></p>
                                            </div>
                                        </div>  
                                        
                                        <div class="row p-4">
                                            <div style="text-align:left;left:30px" class="w2ui-buttons">
                                                <button onclick="saveUser();" name="Save" class="w2ui-btn w2ui-btn-blue" style="" tabindex="11">Add Event Organiser</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>   
                            </div>
                        </div>
                    </div>             

                </div>
              </div>  
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>
    </div>
</main>
</body>
<script>
const API_URL = "<?php echo $_ENV['API_URL']; ?>";
const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";
// create a save function for the event organiser form
function saveUser() {
    var name = document.getElementById('eventu-name').value;
    var surname = document.getElementById('eventu-surname').value;
    var email = document.getElementById('eventu-email').value;
    var contact = document.getElementById('eventu-contact').value;
    var confirm = document.getElementById('eventu-confirm').checked;

    if (name == '' || surname == '' || email == '' || contact == '') {
        gimmeToast('Please fill in all fields', 'error');
    } else {
        // show the loader
        document.getElementById('loaderContainer').style.display = 'flex';

        // send the data to the server
        $.ajax({
            url: API_URL+'api/v1/event_organisers/add_organiser.php',
            type: 'POST',
            data: JSON.stringify({
                name: name,
                surname: surname,
                email: email,
                contact: contact,
                confirm: confirm,
                token: TOKEN
            }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            headers: {
                "Access-Control-Allow-Origin": "*",
            },
            crossDomain: true,
            success: function (response) {
                // hide the loader
                document.getElementById('loaderContainer').style.display = 'none';

                // Display success
                gimmeToast('Event Organiser Added','success');

                // reset the form
                document.getElementById('eventu-name').value = '';
                document.getElementById('eventu-surname').value = '';
                document.getElementById('eventu-email').value = '';
                document.getElementById('eventu-contact').value = '';
                document.getElementById('eventu-confirm').checked = false;
            },
            error: function (error) {
                // hide the loader
                document.getElementById('loaderContainer').style.display = 'none';
                console.log(error)
                gimmeToast('Error Adding Event Organiser', 'error');
            }
        });
    }
}
</script>    
</html>
