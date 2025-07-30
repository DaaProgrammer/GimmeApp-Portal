<?php 
$pageTitle = "Matches";
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
                    <h6 class="text-white text-capitalize ps-3">Match Play</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    <div class="card-header pb-3 px-3">
                        <p class="mb-0">Manage app matches and related data.</p>
                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="users" class="w-100 px-6">
                        <div id="grid" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden;"></div>
                        <div class="actions">
                           
                        </div>
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
// const usersContainer = document.getElementById('users');

// Load all courses for input
var courses = [];
$.ajax({
    url: API_URL + "api/v1/courses/admin_golf_courses.php",
    method: "POST",
    headers: {
        "Access-Control-Allow-Origin": "*",
        "Content-Type": "application/json"
    },
    data: JSON.stringify({
        token: TOKEN
    }),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    success: function(response) {
        console.log("Courses Retrieved Successfully");

        courses = response.course_data.map(course => ({ text: course.course_name, id: course.id }));
        // Test
        console.log(courses);

        // Instantiate W2UI field
        new w2field('list', {
            el: query('input[type=list]')[1],
            items: courses,
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                console.log('Selected:', event.detail.item);
                console.log(document.getElementById("event-course").getAttribute('data-selected-index'));
            }
        })
    },
    error: function(error) {
        console.log("Error Retrieving Courses");
        console.log(error);
    }
});


let grid = new w2grid({
    name: 'grid',
    box: '#grid',
    header: 'Scorecards - Matches & Events',
    reorderRows: false,
    show: {
        header: true,
        footer: true,
        toolbar: true,
        lineNumbers: true
    },
    columns: [
        { field: 'event_name', text: 'Event Name', size: '150px', sortable: true},
        { field: 'event_badge', text: 'Event Badge', size: '150px', sortable: true},
        { field: 'event_date_time', text: 'Event Date Time', size: '200px', sortable: true},
        { field: 'event_status', text: 'Event Status', size: '200px', sortable: true},
        { field: 'event_course', text: 'Event Course', size: '200px', sortable: true },
        { field: 'actions', text: 'Actions', size: '200px', sortable: false },
    ],
    searches: [
        { field: 'event_name', label: 'Event Name'},
        { type: 'int',  field: 'id', label: 'ID' },
        { type: 'date', field: 'event_date_time', label: 'Event Date Time' },
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

            // Process and add new records
            data.event_data.forEach(function(event) {
                grid.add({
                    recid:                      event.id,
                    event_name:                 event.event_name,
                    event_badge:                event.event_badge ? `<span>${event.event_badge}</span>` : `<i class="material-icons">event</i>`,
                    event_date_time:            event.event_date_time,
                    event_status:               `<div class="event-status-${event.event_status}">${window.toTitleCase(event.event_status)}</div>`,
                    event_course:               event.gimme_courses.course_name,
                    actions:                    `
                        <form action="/match-game-scores/" method="POST">
                            <input type="hidden" name="event_id" id="event_id" value="${event.id}">
                            <input type="hidden" name="event_name" id="event_name" value="${event.event_name}">
                            <input type="hidden" name="event_scoring" id="event_scoring" value="${event.event_scoring}">
                            <input type="submit" style="width:100%;padding:5px;color:white;background-color:#00b240;border:none;border-radius:12px;" class="w2ui-btn w2ui-btn-blue" value="${event.event_status == 'complete' ? 'View Game Scores' : 'View Live Scores'}" />
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
