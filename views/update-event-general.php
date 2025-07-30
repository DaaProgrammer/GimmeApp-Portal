<div id="settings-general">
    <!-- Loader Container -->
    <div id="general-loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
        <div class="loader"></div>
    </div>

    <div class="form">
        <div class="form-input">
            <label for="event-name">Event Name</label>
            <input class="w2ui-input" type="text" name="event-name" id="event-name" value="<?= $currentEvent->event_name ?>" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
        </div>

        <div class="form-input">
            <label for="event-code">Event Code</label>
            <input disabled class="w2ui-input" type="text" name="event-code" id="event-code" value="<?= $currentEvent->event_code ?>" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
        </div>

        <div class="form-input">
            <label for="event-date">Event Date</label>
            <input class="w2ui-input" type="eu-datetime" name="event-date" id="event-date" value="<?= $currentEvent->event_date_time ?>" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
        </div>

        <div class="form-input">
            <label for="event-course">Event Course</label>
            <input class="w2ui-input" type="list" name="event-course" id="event-course" value="<?= $currentEvent->gimme_courses->course_name ?>" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
        </div>

        <div class="form-input">
            <label for="event-handicap">Event Handicap</label>
            <input class="w2ui-input" type="list" name="event-handicap" id="event-handicap" value="<?= $currentEvent->event_handicap ?>" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
        </div>

        <div class="form-input">
            <label for="event-participants">Event Participants</label>
            <input class="w2ui-input" type="list" name="event-participants" id="event-participants" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
        </div>

        <!-- Handle save event -->
        <div class="form-save">
            <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
            <button class="w2ui-btn w2ui-action" id="save-settings-general">Save Changes</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="module">

    // Loader Container
    const loaderContainer = document.getElementById('general-loaderContainer');
    loaderContainer.style.display = 'flex';

    // All get requests for input options
    // --------------------------------------------------------------------------------------------------------------------
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

            loaderContainer.style.display = 'none';
            
            // Instantiate courses field
            courses = response.course_data.map(course => ({ id: course.id, text: course.course_name }));

            console.log(courses);

            const course = new w2field('list', {
                el: query('input[type=list]')[0],
                items: courses,
                match: 'contains',
                markSearch: true,
                onSelect(event) {
                    console.log('Selected:', event.detail.item)
                }
            });

            // Get current course
            const currentCourseID = <?= $currentEvent->event_course ?>;
            console.log(currentCourseID)
            const selectedCourse = courses.find(course => course.id === currentCourseID);

            console.log(selectedCourse)

            // Set default selected course
            course.set(selectedCourse);
        },
        error: function(error) {
            console.log("Error Retrieving Courses");
            console.log(error);
        }
    });
    // --------------------------------------------------------------------------------------------------------------------
    // Instantiate W2UI elements here
    new w2field('datetime', { el: query('input[type=eu-datetime]')[0], format: 'dd.mm.yyyy|h24:mm' })
    const handicap = new w2field('list', {
        el: query('input[type=list]')[1],
        items: [
            {
                text:'Gross',
                id:'gross'
            },
            {
                text:'Net',
                id:'net'
            },
            {
                text:'Gross + Net',
                id:'gross+net'
            }
        ],
        match: 'contains',
        markSearch: true,
        onSelect(event) {
            console.log('Selected:', event.detail.item)
            document.getElementById('event-handicap').setAttribute('data-value', event.detail.item.id);
        }
    });
    const participants = new w2field('list', {
        el: query('input[type=list]')[2],
        items: ['Men','Women'],
        match: 'contains',
        markSearch: true,
        onSelect(event) {
            console.log('Selected:', event.detail.item)
            document.getElementById('event-participants').setAttribute('data-value', event.detail.item.id);
        }
    });

    // Set default values
    handicap.set({<?= 'id:"'.$currentEvent->event_handicap.'",text: "'.ucwords(strtolower($currentEvent->event_handicap)).'"' ?>});
    document.getElementById('event-handicap').setAttribute('data-value', "<?= $currentEvent->event_handicap ?>");

    participants.set({<?= 'id:"'.$currentEvent->event_participants.'",text: "'.ucwords(strtolower($currentEvent->event_participants)).'"' ?>});
    document.getElementById('event-participants').setAttribute('data-value', "<?= $currentEvent->event_participants ?>");

    // Do not edit below this.

    function saveSettingsGeneral() {
        const eventName = document.getElementById('event-name').value;
        const eventCode = document.getElementById('event-code').value;
        const eventDate = document.getElementById('event-date').value;
        // const eventCourse = document.getElementById('event-course').value;
        const eventCourse = 1;
        const eventHandicap = document.getElementById('event-handicap').getAttribute('data-value');
        const eventParticipants = document.getElementById('event-participants').getAttribute('data-value');

        if (!eventName || !eventCode || !eventDate || !eventCourse || !eventHandicap || !eventParticipants) {
            alert('All fields are required');
            console.log(eventName);
            console.log(eventCode);
            console.log(eventDate);
            console.log(eventCourse);
            console.log(eventHandicap);
            console.log(eventParticipants);
            return;
        }

        const data = JSON.stringify({
            token: TOKEN,
            id: <?= $_POST['event_id'] ?>,
            event_name: eventName,
            event_code: eventCode,
            event_date_time: eventDate,
            event_course: eventCourse,
            event_handicap: eventHandicap,
            event_participants: eventParticipants
        });

        document.querySelector()

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/event/update_event.php',
            method: 'POST',
            data:data,
            success: function(response) {
                console.log(response);
                gimmeToast('Settings saved','success');
            },
            error: function(error) {
                console.error(error);
                gimmeToast('Settings not saved','error');
            }
        });
    }
    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
    document.querySelector('#save-settings-general').addEventListener("click",saveSettingsGeneral);
    <?php endif?>
</script>
