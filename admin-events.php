<?php 
$pageTitle = "Events";
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

.actions{
    margin-top: 20px;
    display:flex;
    justify-content:right;
    align-items:center;
}
#event-registration-deadline{
    cursor: not-allowed;
    font-weight:bold;
}
.event-type-team{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #d7d7d7;
}
.event-type-individual{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #00b240;
    color:white;
}
.event-status-pending{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #d7d7d7;
    font-weight:bold;
}
.event-status-active{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #ffa041;
    color: black;
    font-weight:bold;
}
.event-status-complete{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color:#00b240;
    color:black;
    font-weight:bold;
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
                    <h6 class="text-white text-capitalize ps-3">Events</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    <div class="card-header pb-3 px-3">
                        <p class="mb-0">View key event data.</p>
                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="users" class="w-100 px-6">
                        <div id="grid" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden;"></div>
                    </div>

                </div>
              </div> 
            
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>
    </div>

</main>
<script type='module' src='../util/util.js'></script>
<script type="module">
const API_URL = "<?php echo $_ENV['API_URL']; ?>";
const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";  

// Loader Container
const loaderContainer = document.getElementById('loaderContainer');
loaderContainer.style.display = 'flex';
const usersContainer = document.getElementById('users');

let grid = new w2grid({
    name: 'grid',
    box: '#grid',
    header: 'App Users & Event Organisers',
    reorderRows: false,
    show: {
        header: true,
        footer: true,
        toolbar: true,
        lineNumbers: true
    },
    columns: [
        { field: 'event_name', text: 'Event Name', size: '150px', sortable: true},
        { field: 'event_type', text: 'Event Type', size: '150px', sortable: true},
        { field: 'event_badge', text: 'Event Badge', size: '150px', sortable: true},
        { field: 'event_code', text: 'Event Code', size: '150px', sortable: true, clipboardCopy: true,},
        { field: 'event_description', text: 'Event Description', size: '200px', sortable: true},
        { field: 'event_date_time', text: 'Event Date Time', size: '200px', sortable: true},
        { field: 'event_participants', text: 'Event Participants', size: '200px', sortable: true},
        { field: 'event_status', text: 'Event Status', size: '200px', sortable: true},
        { field: 'event_course', text: 'Event Course', size: '200px', sortable: true },
        { field: 'actions', text: 'Actions', size: '200px', sortable: false },
    ],
    searches: [
        { field: 'event_name', label: 'Event Name'},
        { type: 'int',  field: 'id', label: 'ID' },
        { field: 'event_code', label: 'Event Code' },
        { field: 'event_description', label: 'Event Description' },
        { type: 'date', field: 'event_date_time', label: 'Event Date Time' },
        { field: 'event_participants', label: 'Event Participants' },
        { field: 'event_status', label: 'Event Status' },
        { field: 'event_course', label: 'Event Course' },
    ],
    onExpand(event) {
        query('#'+event.detail.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>')
    }
});

// create an ajax request using jQuery
$.ajax({
    url: API_URL+'api/v1/event/organiser_events.php',
    dataType: 'json',
    type: 'POST',
    data: JSON.stringify({
        token: TOKEN
    }),
    success: function (data) {
        console.log(data);
        if (data && data.event_data) {
            // Clear existing records
            grid.clear();
            loaderContainer.style.display = 'none';
            usersContainer.style.display = 'block';

            // Process and add new records
            data.event_data.forEach(function(event) {
                grid.add({
                    recid:                      event.id,
                    event_name:                 event.event_name,
                    event_type:                 `<div class="event-type-${event.event_type}">${window.toTitleCase(event.event_type)}</div>`,
                    event_badge:                event.event_badge ? event.event_badge : `<i class="material-icons">event</i>`,
                    event_code:                 `<div style="font-weight:bold">${event.event_code}</div>`,
                    event_description:          event.event_description,
                    event_date_time:            event.event_date_time,
                    event_participants:         `<div style="text-align:center">${window.toTitleCase(event.event_participants)}</div>`,
                    event_status:               `<div class="event-status-${event.event_status}">${window.toTitleCase(event.event_status)}</div>`,
                    event_course:               event.gimme_courses.course_name,
                    actions:                    `
                        <form action="/update-event/" method="POST">
                        <input type="hidden" name="event_id" value="${event.id}">
                        <input type="submit" class="w2ui-btn w2ui-btn-blue" style="width:100%;padding:5px;color:white;background-color:#00b240;border:none;border-radius:12px;" value="View" />
                        </form>
                    `
                });
            });

            grid.refresh();
        }
    }
});
</script>
</body>
</html>
