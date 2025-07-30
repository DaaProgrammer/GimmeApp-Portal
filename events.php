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
                        <p class="mb-0">Manage app users and view key user data.</p>
                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="users" class="w-100 px-6">
                        <div id="grid" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden;"></div>
                        <div class="actions">
                            <button class="w2ui-btn w2ui-btn-blue" id="createModalBtn">
                                Create New Event
                            </button>
                        </div>
                    </div>

                <!-- Create Form -->
                <div id="create-event-container" style="display: none; padding: 20px">
                    <div rel="title">
                        <h3>Create an Event</h3>
                        <p>Please note that the event code will be automatically generated, once you have completed the form below.</p>
                    </div>
                    <div rel="body" style="padding: 10px; line-height: 150%">
                        <div id="toolbar"></div>
                        <section>
                            <h4>Event Details</h4>
                            <div class="w2ui-field">
                                <label for="event_name">Event Name</label>
                                <div><input id="event_name" name="event_name" class="w2ui-input" style="width: 300px" type="text" tabindex="1"></div>
                            </div>
                            
                            <div class="w2ui-field dt">
                                <label>Event Date:</label>
                                <div> <input type="eu-datetime" id='event-date' class="w2ui-input"> </div>
                            </div>

                            <div class="w2ui-field">
                                <label>Participants</label>
                                <div> <input type="list" id="participants" class="w2ui-input"> </div>
                            </div>

                            <div class="w2ui-field">
                                <label for="event_description">Event Description</label>
                                <div><textarea id="event_description" name="event_description" class="w2ui-input" style="width: 300px" type="text" tabindex="1"></textarea></div>
                            </div>

                        </section>
                        <hr/>
                        <section>
                            <h4>Course Setup</h4>
                            <div class="w2ui-field">
                                <label>Course</label>
                                <div> <input id="event-course" class="w2ui-input" type="list"></div>
                            </div>
                            <div class="w2ui-field">
                                <label>Holes</label>
                                <div id="radio-container">
                                    <input id="holes" class="w2ui-input" type="list">
                                </div>
                            </div>
                            <p class="mt-0">Default Tees</p>
                            <div class="w2ui-field">
                                <label>Men</label>
                                <div id="radio-container">
                                    <input id="men-defauult-tees" class="w2ui-input" type="list">
                                </div>
                            </div>
                            <div class="w2ui-field">
                                <label>Women</label>
                                <div id="radio-container">
                                    <input id="women-defauult-tees" class="w2ui-input" type="list">
                                </div>
                            </div>
                        </section>
                        <hr/>
                        <section>
                            <h4>Registration</h4>
                            <div class="w2ui-field ">
                                <label>Deadline</label>
                                <div id="event-registration-deadline" title="This is automatically set to 24 hours **BEFORE** the event date.">**24 hours before event date**</div>
                            </div>
                            <div class="w2ui-field">
                                <label>Max Participants</label>
                                <div><input type="number" id="max-participants" class="w2ui-input" min='0' max='100'></div>
                            </div>
                        </section>
                        <hr/>
                        <section>
                            <h4>Scoring</h4>
                            <div class="w2ui-field">
                                <label>Participants Type</label>
                                <div>
                                    <input id="individual-or-team" class="w2ui-input" type="list">
                                </div>
                            </div>
                            <div id="individual-participants" style="display:none">
                                <div class="w2ui-field">
                                    <label>Scoring</label>
                                    <div>
                                        <input id="scoring" class="w2ui-input" type="list">
                                    </div>
                                </div>
                                <div class="w2ui-field">
                                    <label>Handicaps</label>
                                    <div>
                                        <input id="handicaps" class="w2ui-input" type="list">
                                    </div>
                                </div>
                            </div>
                            <div id="team-participants" style="display:none">
                                <div class="w2ui-field">
                                    <label>Format</label>
                                    <div>
                                        <input id="format" class="w2ui-input" type="list">
                                    </div>
                                </div>
                                <div class="w2ui-field">
                                    <label>Scoring</label>
                                    <div>
                                        <input id="team-scoring" class="w2ui-input" type="list">
                                    </div>
                                </div>

                                <div class="w2ui-field">
                                    <label>Number per team</label>
                                    <div>
                                        <input id="number-per-team" class="w2ui-input" type="list">
                                    </div>
                                </div>

                                <div class="w2ui-field">
                                    <label>Number of teams</label>
                                    <div>
                                        <input id="number-of-teams" class="w2ui-input" type="list">
                                    </div>
                                </div>

                                <div class="w2ui-field">
                                    <label>Handicaps</label>
                                    <div>
                                        <input id="team-handicaps" class="w2ui-input" type="list">
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div rel="buttons">
                        <button class="w2ui-btn w2ui-action" id="close-form-btn">Close</button>
                        <button class="w2ui-btn w2ui-btn-blue" id="create-event-button">Confirm</button>
                    </div>
                </div>
                <!-- END Create Form -->

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
        courses = response.course_data.map(course => ({ text: course.course_name, id: course.id , tee_data: course.tee_data}));
        console.log(courses);

        // Instantiate default tee fields - and leave blank until course is selected
        // Men
        let menDefaultTees = new w2field('list', {
            el: document.querySelector("#men-defauult-tees"),
            items: ['Please select a course first'],
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                if(event.detail.item.id === womenDefaultTees.selected.id){
                    menDefaultTees.selected = {};
                    gimmeToast('Cannot select the same tee for both genders', 'error');
                }
            }
        })
        // Women
        let womenDefaultTees = new w2field('list', {
            el: document.querySelector("#women-defauult-tees"),
            items: ['Please select a course first'],
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                if(event.detail.item.id === menDefaultTees.selected.id){
                    womenDefaultTees.selected = {};
                    gimmeToast('Cannot select the same tee for both genders', 'error');
                }
            }
        })

        // Instantiate course select field
        new w2field('list', {
            el: query('input[type=list]')[1],
            items: courses,
            match: 'contains',
            markSearch: true,
            onSelect(event) {

                console.log(event)
                document.querySelector("#event-course").setAttribute('data-value', event.detail.item.id);

                const teeData = Object.keys(event.detail.item.tee_data.tees).map(tee => ({text: tee}));

                // Toggle tee data on default tee fields
                menDefaultTees.options.items = teeData;
                womenDefaultTees.options.items = teeData;
            }
        })
    },
    error: function(error) {
        console.log("Error Retrieving Courses");
        console.log(error);
    }
});


// Create Form
new w2field('datetime', { 
    el: query('input[type=eu-datetime]')[0]
});
// Courses field found in courses AJAX request above
new w2field('list', {
    el: query('input[type=list]')[2],
    items: ['18','front-nine','back-nine'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
new w2field('list', {
    el: document.querySelector('#participants'),
    items: ['men','woman'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
new w2field('list', {
    el: document.querySelector("#individual-or-team"),
    items: ['individual', 'team'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
        if(event.detail.item.text == "individual"){
            $('#team-participants').hide(500);
            $('#individual-participants').show(500);
        }else{
            $('#individual-participants').hide(500);
            $('#team-participants').show(500);
        }
    }
});

//scoring
new w2field('list', {
    el: document.querySelector("#scoring"),
    items: ['strokeplay','stableford'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
//handicaps
new w2field('list', {
    el: document.querySelector("#handicaps"),
    // items: ['gross','net','gross+net'],
    items: ['gross'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
//format
new w2field('list', {
    el: document.querySelector("#format"),
    // items: ['best-ball','scramble','skins'],
    items: ['best-ball'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})

new w2field('list', {
    el: document.querySelector("#team-scoring"),
    items: ['strokeplay','stableford'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
new w2field('list', {
    el: document.querySelector("#team-handicaps"),
    // items: ['gross','net','gross+net'],
    items: ['gross'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
// Team specific
new w2field('list', {
    el: document.querySelector("#number-per-team"),
    items: ['2','3','4'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)

        let maxParticipants = document.querySelector('#max-participants')
        let numberPerTeam = parseInt(document.querySelector("#number-per-team").value);

        if(maxParticipants.value == null){
            max_participants.value = numberPerTeam;
        }

        if(numberPerTeam > (maxParticipants.value / 2)){
            // Fix max participants value and return with error
            maxParticipants.value = numberPerTeam
            return;
        }
    }
})
new w2field('list', {
    el: document.querySelector("#number-of-teams"),
    items: ['2','3','4'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
        //event.detail.item.id

        let maxParticipants = document.querySelector('#max-participants')
        let numberPerTeam = parseInt(document.querySelector("#number-per-team").value);
        let numberOfTeams = parseInt(event.detail.item.text);

        if(maxParticipants.value == null){
            max_participants.value = numberPerTeam;
        }

        if(numberPerTeam * numberOfTeams != maxParticipants){
            // Fix max participants value and return with error
            maxParticipants.value = numberPerTeam * numberOfTeams;
            return;
        }
    }
})

// End Create Form

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
    },
    error: function (error){
        console.log(error)
    }
});

function createEvent(){
    // Capture event name
    var eventName = document.getElementById("event_name").value;
    if (!eventName) {
        alert("Event name is required");
        return;
    }

    // Capture event date time
    var eventDateTime = document.querySelector("#event-date").value;
    if (!eventDateTime) {
        alert("Event date time is required");
        return;
    }

    // Capture event participants
    var eventParticipants = document.querySelector("#participants").value;
    if (!eventParticipants) {
        alert("Event participants is required");
        return;
    }

    // Capture event description
    var eventDescription = document.querySelector("#event_description").value;
    if (!eventDescription) {
        alert("Event description is required");
        return;
    }

    // Capture event course
    var eventCourse = document.querySelector("#event-course").getAttribute('data-value');
    if (!eventCourse) {
        alert("Event course is required");
        return;
    }

    // Capture event holes
    var eventHoles = document.getElementById("holes").value;
    if (!eventHoles) {
        alert("Event holes is required");
        return;
    }

    // Capture event default tees
    var eventDefaultTeesMen = document.getElementById("men-defauult-tees").value;
    var eventDefaultTeesWomen = document.getElementById("women-defauult-tees").value;
    if (!eventDefaultTeesMen || !eventDefaultTeesWomen) {
        alert("Event default tees are required");
        return;
    }

    // Capture event registration deadline
    var eventRegistrationDeadline = document.getElementById("event-registration-deadline").textContent;
    if (!eventRegistrationDeadline) {
        alert("Event registration deadline is required");
        return;
    }

    // Capture event type
    var eventType = document.getElementById("individual-or-team").value;
    if (!eventType) {
        alert("Event type is required");
        return;
    }
    var maxParticipants = document.querySelector('#max-participants').value
    if(!maxParticipants){
        alert("Max participants is required");
        return;
    }

    var handicap = document.querySelector('#handicaps').value

    // Capture event team format and other team related fields if event type is team
    if(eventType == 'team'){
        var eventTeamFormat = document.getElementById("format").value;
        if (!eventTeamFormat) {
            alert("Event team format is required");
            return;
        }

        var playerPerTeam = document.getElementById("number-per-team").value;
        if (!playerPerTeam) {
            alert("Event max players is required");
            return;
        }

        var eventMaxTeams = document.getElementById("number-of-teams").value;
        if (!eventMaxTeams) {
            alert("Event max teams is required");
            return;
        }

        var eventAutoAssign = document.getElementById("team-handicaps").value;
        if (!eventAutoAssign) {
            alert("Event auto assign is required");
            return;
        }

        var scoring = document.querySelector('#team-scoring').value;
        if (!scoring) {
            alert("Scoring is required");
            return;
        }

        handicap = document.querySelector('#team-handicaps').value
    }else{
        var scoring = document.querySelector('#scoring').value;
        if (!scoring) {
            alert("Scoring is required");
            return;
        }
        handicap = document.querySelector('#handicaps').value;
    }

    // Build JSON
    var data;
    if(eventType == 'individual'){
        data = {
            token: TOKEN,
            event_name: eventName,
            event_description: eventDescription,
            event_date_time: eventDateTime,
            event_participants: eventParticipants,
            event_course: eventCourse,
            event_holes: eventHoles,
            event_default_tees: {men: eventDefaultTeesMen, women: eventDefaultTeesWomen},
            event_registration_date:eventRegistrationDeadline,
            event_type: eventType,
            // TEAM
            event_team_format: null,
            // event_max_players: playerPerTeam,
            event_auto_assign: null,
            // Missed post inputs
            event_max_players: maxParticipants,
            event_scoring: scoring,
            event_handicap: handicap
        }
    }else{
        data = {
            token: TOKEN,
            event_name: eventName,
            event_description: eventDescription,
            event_date_time: eventDateTime,
            event_participants: eventParticipants,
            event_course: eventCourse,
            event_holes: eventHoles,
            event_default_tees: {men: eventDefaultTeesMen, women: eventDefaultTeesWomen},
            event_registration_date:eventRegistrationDeadline,
            event_type: eventType,
            // TEAM
            event_team_format: eventType == 'team' ? eventTeamFormat : null,
            // event_max_players: playerPerTeam,
            event_auto_assign: eventType == 'team' ? eventAutoAssign : null,
            // Missed post inputs
            event_max_players: maxParticipants,
            event_scoring: scoring,
            event_handicap: handicap,
            event_player_per_team: playerPerTeam,
            event_max_teams: eventMaxTeams
        }
    }

    document.querySelector('#create-event-button').disabled = true;
    document.querySelector('#create-event-button').innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
    document.querySelector('#close-form-btn').disabled = true;

    $.ajax({
        url: API_URL+"api/v1/event/create_event.php",
        method: "POST",
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Content-Type": "application/json"
        },
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success:function(response){
            console.log("Result");
            console.log(response);
            gimmeToast("Event Created Successfully", "success");
            // refresh the page
            setTimeout(function(){
                location.reload();
            }, 1000);
        },
        error:function(error){
            console.log("Error");
            console.log(error);
            gimmeToast("Error Creating Event", "error");

            setTimeout(function(){
                document.querySelector('#create-event-button').disabled = false;
                document.querySelector('#create-event-button').innerHTML = 'Confirm';
                document.querySelector('#close-form-btn').disabled = false;
            },1000);
        }
    });
}

function closeEventForm(){
    $('#create-event-container').hide(500);
    $('#createModalBtn').show(500);
    $('#grid').show(500);
}

function updateEventRegistrationDeadline(eventDatetime){
    const eventRegDeadlineContainer = document.querySelector('#event-registration-deadline');
    const eventDateTime = new Date(eventDatetime);
    eventDateTime.setHours(eventDateTime.getHours() - 24);
    const eventRegDeadline = eventDateTime.toLocaleString();
    eventRegDeadlineContainer.textContent = eventRegDeadline;
}
document.querySelector('#event-date').addEventListener('change',function(event){
    updateEventRegistrationDeadline(event.target.value);
});
document.querySelector("#createModalBtn").addEventListener('click', function() {
    // Add your event handling code here
    $('#create-event-container').show(500);
    $('#createModalBtn').hide(500);
    $('#grid').hide(500);
});
document.querySelector("#close-form-btn").addEventListener("click", closeEventForm);

document.querySelector('#create-event-button').addEventListener("click",function(){
    createEvent();
});
</script>
</body>
</html>
