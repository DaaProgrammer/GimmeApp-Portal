<div id="settings-scoring" style="display:none">
    <!-- Loader Container -->
    <div id="scoring-loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
        <div class="loader"></div>
    </div>
    <div id="event-golfers" class="w-100 px-6">
        <div id="event-scoring-table" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden; margin-top: 30px;"></div>
        <div class="actions" style="justify-content:left">
        <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
            <button class="w2ui-btn w2ui-btn-blue" id="scoring-settings-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                    <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                </svg>
                <span>Settings</span>
            </button>
            <?php
                if($currentEvent->event_status == "pending"){
                    echo <<<EOD
                    <button type="button" class="w2ui-btn w2ui-btn-orange" id="start-game-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                        </svg>
                        <span>Start Game</span>
                    </button>
                    EOD;
                } else if($currentEvent->event_status == "active"){
                    echo <<<EOD
                    <button type="button" class="w2ui-btn w2ui-btn-red" id="complete-game-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                        </svg>
                        <span>Complete Game</span>
                    </button>
                    EOD;
                }
            ?>
        <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="scoring-settings-modal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scoring Settings</h5>
                </div>
                <div class="modal-body">
                    <div class="form">
                        <div class="form-input">
                            <label for="event-scoring-format">Format</label>
                            <input class="w2ui-input" type="text" name="event-scoring-format" id="event-scoring-format" data-value="<?= $currentEvent->event_scoring ?>">
                        </div>
                        <div class="form-input">
                            <label for="event-scoring-handicaps">Handicaps</label>
                            <input class="w2ui-input" type="text" name="event-scoring-handicaps" id="event-scoring-handicaps" data-value="<?= $currentEvent->event_handicap ?>">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="enable-custom-settings" <?= $currentEvent->course_data->settings->custom_data ? 'checked' : '' ?> >
                            <label class="form-check-label" for="enable-custom-settings">Custom Settings</label>
                        </div>

                        <div id="scoring-modal-settings--custom-settings" style="display: <?= $currentEvent->course_data->settings->custom_data ? 'block' : 'none' ?>">
                            <h3>Custom settings</h3>
                            <div id="scoring-modal-settings--custom-settings--container">
                                <?php
                                // echo json_encode($currentEvent->course_data->course_data->hole_data);

                                // Check if custom settings is already enabled
                                if($currentEvent->course_data->settings->custom_data == true){
                                    foreach ($currentEvent->course_data->settings->course_data as $key => $hole) {

                                        // Format key to be readable
                                        $formattedKey = ucwords(str_replace('_', ' ', $key));

                                        list($latitude, $longitude) = explode(", ", $hole->pin_location_gps);

                                        // Output markup to edit latitude and longitde of each hole
                                        echo <<<EOD
                                            <div class="custom-settings--hole" data-key="$formattedKey">
                                                <h6>$formattedKey</h6>
                                                <div class='pin-gps-input-container latitude-input'>
                                                    <label> Latitude </label>
                                                    <input type="number" class="latitude" value="$latitude">
                                                </div>
                                                <div class='pin-gps-input-container longitude-input'>
                                                    <label> Longitude </label>
                                                    <input type="number" class="longitude" value="$longitude">
                                                </div>
                                            </div>
                                        EOD;
                                    }
                                }else{
                                    foreach ($currentEvent->course_data->course_data->hole_data as $key => $hole) {

                                        // Format key to be readable
                                        $formattedKey = ucwords(str_replace('_', ' ', $key));

                                        list($latitude, $longitude) = explode(", ", $hole->pin_location_gps);

                                        // Output markup to edit latitude and longitde of each hole
                                        echo <<<EOD
                                            <div class="custom-settings--hole" data-key="$formattedKey">
                                                <h6>$formattedKey</h6>
                                                <div class='pin-gps-input-container latitude-input'>
                                                    <label> Latitude </label>
                                                    <input type="number" class="latitude" value="$latitude">
                                                </div>
                                                <div class='pin-gps-input-container longitude-input'>
                                                    <label> Longitude </label>
                                                    <input type="number" class="longitude" value="$longitude">
                                                </div>
                                            </div>
                                        EOD;
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="scoring-dismiss-modal-btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save-scoring-settings-btn">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="edit-scorecard-modal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Scorecard</h5>
                </div>
                <div class="modal-body">
                    <div class="form">
                        <input type="hidden" name="" id="user_id">
                        <table id="edit-scorecard-modal--table">
                            <thead>
                                <tr>
                                    <?php
                                        // Check how many holes are being played for this event
                                        $hole_start;
                                        $hole_end;
                                        switch ($currentEvent->event_holes) {
                                            case 'front-nine':
                                                $hole_start = 1;
                                                $hole_end = 9;
                                                break;

                                            case 'back-nine':
                                                $hole_start = 10;
                                                $hole_end = 18;
                                                break;
                                            
                                            case 18:
                                                $hole_start = 1;
                                                $hole_end = 18;
                                                break;
                                                
                                            default:
                                                $hole_start = 0;
                                                $hole_end = 0;
                                                break;
                                        }

                                        echo "<td> Hole </td>";
                                        
                                        for ($i=$hole_start; $i <= $hole_end; $i++) { 
                                            echo <<<EOD
                                                <td> $i </td>
                                            EOD;
                                            if ($i == 9) {
                                                echo "<td class='scorecard-label-helper'> OUT </td>";
                                            }
                                        }
                                        if ($hole_end == 18) {
                                            echo "<td class='scorecard-label-helper'> IN </td>";
                                        }

                                    ?>
                                </tr>
                                <tr>
                                    <?php
                                        $hole_data = $currentEvent->course_data->course_data->hole_data;
                                        $sum_par = 0;

                                        echo "<td> Par </td>";

                                        for ($i = $hole_start; $i <= $hole_end; $i++) {
                                            $hole = "hole_" . $i;
                                            $par = $hole_data->{$hole}->par;
                                            $sum_par += $par;
                                            echo "<td> $par </td>";

                                            if ($i == 9 || $i == 18) {
                                                echo "<td class='scorecard-label-helper'> $sum_par </td>";
                                            }
                                        }


                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="scoreboard-row">
                                    <td>
                                    Gross
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning edit-scorecard-btn" id="scorecard-clear-btn" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-danger edit-scorecard-btn" id="scorecard-withdraw-btn">Withdraw/DQ</button>
                    <button type="button" class="btn btn-secondary" id="scorecard-dismiss-modal-btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary edit-scorecard-btn" id="save-scorecard-settings-btn">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

<script type="module">

    // Loader Container
    const scoresLoaderContainer = document.getElementById('scoring-loaderContainer');
    scoresLoaderContainer.style.display = 'none';

    // Modal
    const scoringModal = $('#scoring-settings-modal');
    const scoreCardModal = $('#edit-scorecard-modal');

    const number_of_holes = <?= $currentEvent->event_holes == 18 ? 18 : 9 ?>;
    var userScores;

    // --------------------------------------------------------------------------------------------------------------------
    // Table
    window.initializeScoringTable = function() {
        window.scoring_grid = new w2grid({
            name: 'event-scoring-table',
            box: '#event-scoring-table',
            header: 'Event Scoring',
            reorderRows: false,
            show: {
                header: true,
                footer: true,
                toolbar: true,
                lineNumbers: true
            },
            columns: [
                { field: 'index', text: 'Index', sortable: true },
                { field: 'name', text: 'Name', sortable: true },
                { field: 'email', text: 'Email', sortable: true }, 
                { field: 'status', text: 'Status', sortable: true },
                <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                { field: 'action', text: 'Action'}
                <?php endif; ?>
            ],
            searches: [
                { field: 'index', text: 'Index', sortable: true },
                { field: 'name', text: 'Name', sortable: true },
                { field: 'email', text: 'Email', sortable: true }, 
                { field: 'status', text: 'Status', sortable: true },
            ],
            onExpand(event) {
                query('#'+event.detail.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>')
            },
            onClick(event){
                <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                const rowClicked = event.detail.originalEvent.delegate;
                const score = JSON.parse(rowClicked.querySelector("td input").getAttribute('data-score'));

                // Clear previous values
                clearScores()

                // Get updated values
                getScores(score.user.id)

                console.log("Edit modal");

                const dqORsaveBtn = document.querySelector('#scorecard-withdraw-btn');
                
                if(score.status == 'disqualified'){
                    dqORsaveBtn.classList.add("btn-primary");
                    dqORsaveBtn.classList.remove("btn-danger");
                    dqORsaveBtn.innerText = "Enable";
                    document.querySelector('#scorecard-withdraw-btn').addEventListener('click', function(){
                        enablePlayer();
                    });
                }
                else{
                    dqORsaveBtn.classList.add("btn-danger");
                    dqORsaveBtn.classList.remove("btn-primary");
                    dqORsaveBtn.innerText = "Withdraw/DQ";
                    document.querySelector('#scorecard-withdraw-btn').addEventListener('click', function(){
                        disqualifyPlayer()
                    });
                }
                // Check if user can be DQ or Enabled
                scoreCardModal.show(200);
                <?php endif; ?>
            }
        });
    }

    // --------------------------------------------------------------------------------------------------------------------
    window.populateScoringTable = function() {
        // Get users from invitations
        const eventScores = <?= json_encode($scoresList) ?>;

        console.log(eventScores);

        // Process and add new records
        if(eventScores.length > 0){
            eventScores.forEach(function(score,index) {
                window.scoring_grid.add({
                    recid: index,
                    index: index+1,
                    name: `${score.user.name} ${score.user.surname}`,
                    email: score.user.email ? score.user.email : "None",
                    status: score.status.charAt(0).toUpperCase() + score.status.slice(1),
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    action: `
                        <input type="button" class="w2ui-btn w2ui-btn-blue editModalBtn" value="Edit" data-score='${JSON.stringify(score)}' />
                    `
                    <?php endif; ?>
                });
            });
        }
    }

    function saveSettingsScoring() {
        let event_scoring_format = document.querySelector('#event-scoring-format').getAttribute('data-value');
        let event_scoring_handicaps = document.querySelector('#event-scoring-handicaps').getAttribute('data-value');
        let has_custom_settings = document.querySelector('#enable-custom-settings').checked;

        const hole_data = JSON.parse('<?= json_encode($currentEvent->course_data->course_data->hole_data) ?>');

        if(has_custom_settings){
            
            console.log("Initial");
            console.log(hole_data['hole_1'])

            document.querySelectorAll('.custom-settings--hole').forEach((hole, index) => {
                const latitude = hole.querySelector('.latitude').value;
                const longitude = hole.querySelector('.longitude').value;
                const hole_key = 'hole_' + (index + 1); // Assuming hole_key is in the format 'hole_x'
                hole_data[hole_key]['pin_location_gps'] = `${latitude}, ${longitude}`;
            });

            console.log("Updated");
            console.log(hole_data)
        }
        console.log(has_custom_settings)

        const data = {
            token:TOKEN,
            event_id: <?= $_POST['event_id'] ?>,
            user_id: 2,
            has_custom_settings: `${has_custom_settings}`,
            hole_data: has_custom_settings ? hole_data : null,
            scoring: event_scoring_format,
            handicap: event_scoring_handicaps
        };

        console.log(data);

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/event/update_event_scoring.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                console.log(response)
                gimmeToast("Score settings updated",'success');
                window.location.reload();
            },
            error: function(error) {
                console.log(error);
                gimmeToast("Error fetching user scores",'error');
            }
        });
    }
    // --------------------------------------------------------------------------------------------------------------------
    // Score Settings

    function initializeScoreSettings(){
        const format = new w2field('list', {
            el: document.querySelector('#event-scoring-format'),
            items: [
                {id:'strokeplay', text:'Strokeplay'},{id:'stableford', text:'Stableford'}
            ],
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                console.log('Selected:', event.detail.item)
                document.querySelector('#event-scoring-format').setAttribute('data-value', event.detail.item.id);
            }
        });
        format.set({
            <?php
                if($currentEvent->event_scoring == 'strokeplay'){
                    echo "id:'strokeplay',text:'Strokeplay'";
                }else{
                    echo "id:'stableford',text:'Stableford'";
                }
            ?>
        });

        const handicap = new w2field('list', {
            el: document.querySelector('#event-scoring-handicaps'),
            items: [
                {id:'gross', text:'Gross'},{id:'net', text:'Net'},{id:'gross+net', text:'Gross + Net'}
            ],
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                console.log('Selected:', event.detail.item)
                document.querySelector('#event-scoring-handicaps').setAttribute('data-value', event.detail.item.id);
            }
        });
        handicap.set({
            <?php
                if($currentEvent->event_scoring == 'gross'){
                    echo "id:'gross',text:'Gross'";
                }else if($currentEvent->event_scoring == 'net'){
                    echo "id:'net',text:'Net'";
                }else{
                    echo "id:'gross+net',text:'Gross + Net'";
                }
            ?>
        });
    }

    // --------------------------------------------------------------------------------------------------------------------
    // Scorecard
    function getScores(user_id) {
        const data = {
            token:TOKEN,
            event_id: <?= $_POST['event_id'] ?>,
            user_id: user_id
        };

        // Disable all buttons, except for close
        const editModalBtn = document.querySelectorAll('.edit-scorecard-btn');
        editModalBtn.forEach(button => {
            button.disabled = true;
        });

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/scores/get_score.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                editModalBtn.forEach(button => {
                    button.disabled = false;
                });
                populateScores(response.data.score_details)
                document.querySelector('#user_id').value = user_id;
            },
            error: function(error) {
                console.log(error);
                gimmeToast("Error fetching user scores",'error');
            }  
        });
    }
    function completeEvent(){
        const data = {
            token:TOKEN,
            event_id: <?= $_POST['event_id'] ?>,
        };

        // Disable all buttons, except for close
        const editModalBtn = document.querySelectorAll('.edit-scorecard-btn');
        editModalBtn.forEach(button => {
            button.disabled = true;
        });

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/event/complete_event.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if(response.msg == "Success") window.location.reload()
            },
            error: function(error) {
                console.log(error);
                gimmeToast("Error fetching user scores",'error');
            }  
        });
    }
    function populateScores(scoreData){
        const scoreboardRowToPopulate = document.querySelector('#scoreboard-row');
        let shotsMarkup = '';
        let totalShots = 0;
        let holeValues = {};

        console.log(scoreData);

        // Create an object to easily access hole values
        scoreData.holes.forEach(hole => {
            if(hole.status == "complete"){
                console.log("Hole ",hole);
                if(hole.total_shots != 0){
                    holeValues[hole.hole_number] = hole.total_shots;
                }else if(hole.quick_sc == true){
                    console.log("Quick Shot on Hole " + hole.hole_number":", hole.quick_shots + hole.quick_putss);
                    holeValues[hole.hole_number] = hole.quick_shots + hole.quick_putss;
                }else{
                    holeValues[hole.hole_number] = hole.shots.length;
                }
            }else{
                holeValues[hole.hole_number] = 0;
            }
        });

        // Loop through the number of holes to create inputs
        for (let i = 1; i <= number_of_holes; i++) {
            const holeValue = holeValues[i] ? holeValues[i] : '';
            shotsMarkup += `<td><input class="scorecard-input" type="number" value="${holeValue}"></td>`;
            totalShots += holeValues[i] ? parseInt(holeValues[i]) : 0;
            if (i % 9 === 0) {
                shotsMarkup += `<td class="scorecard-label-helper total-shots">${totalShots}</td>`; // Add total after every 9 holes
                totalShots = 0; // Reset totalShots for the next set of 9 holes
            }
        }

        console.log('Total Shots:', totalShots);
        scoreboardRowToPopulate.innerHTML = "<td>Gross</td>";
        scoreboardRowToPopulate.innerHTML += shotsMarkup;
        userScores = JSON.stringify(scoreData);

        addInputEventListeners()
    }
    function addInputEventListeners(){
        var inputs = document.querySelectorAll('.scorecard-input');
        for (let i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('change', function(event) {
                calculateNewTotal(event.target.parentElement.parentElement)
            });
        }
    }
    function calculateNewTotal(row){
        console.log(row)

        // Update total for current row
        var inputs = row.querySelectorAll('.scorecard-input');
        let newTotal = 0;
        for (let i = 0; i < inputs.length; i++) {
            newTotal += parseInt(inputs[i].value != "" ? inputs[i].value : "0");
        }
        console.log(newTotal)
        row.querySelector('.total-shots').innerHTML = newTotal.toString();
    }
    function disqualifyPlayer(){
        const data = {
            token:TOKEN,
            event_id: <?= $_POST['event_id'] ?>,
            user_id: document.querySelector('#user_id').value
        };

        // Disable all buttons, except for close
        const editModalBtn = document.querySelectorAll('.edit-scorecard-btn');
        editModalBtn.forEach(button => {
            button.disabled = true;
        });

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/event/disqualify_player.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                editModalBtn.forEach(button => {
                    button.disabled = false;
                });

                window.location.reload();
            },
            error: function(error) {
                console.log(error);
                gimmeToast("Error fetching user scores",'error');
            }  
        });
    }
    function enablePlayer(){
        const data = {
            token:TOKEN,
            event_id: <?= $_POST['event_id'] ?>,
            user_id: document.querySelector('#user_id').value
        };

        // Disable all buttons, except for close
        const editModalBtn = document.querySelectorAll('.edit-scorecard-btn');
        editModalBtn.forEach(button => {
            button.disabled = true;
        });

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/event/enable_player.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                editModalBtn.forEach(button => {
                    button.disabled = false;
                });

                window.location.reload();
            },
            error: function(error) {
                console.log(error);
                gimmeToast("Error fetching user scores",'error');
            }  
        });
    }
    function saveScoreCard(){

        // Get total shots for each hole
        const scoreCardInputs = document.querySelectorAll('.scorecard-input');
        const scoreJSON = JSON.parse(userScores);

        scoreCardInputs.forEach((input, index) => {
            console.log(scoreJSON.holes[index])
            if(scoreJSON.holes[index]){
                scoreJSON.holes[index].total_shots = parseInt(input.value);
                scoreJSON.holes[index].status = "complete";
            }else{
                scoreJSON.holes.push({
                    total_shots: parseInt(input.value),
                    shots:[],
                    status:'complete', // incomplete/complete
                    quick_sc: "details for quick SC",
                    total_puts: 0,
                    condition: {
                        green:'',
                        teebox:''
                    },
                    hole_number: index+1
                })
            }
        });

        console.log("NEW JSON: ");
        console.log(scoreJSON);
        // return;

        const data = {
            token:TOKEN,
            event_id: <?= $_POST['event_id'] ?>,
            user_id: document.querySelector('#user_id').value,
            user_scores: scoreJSON
        };

        // Disable all buttons, except for close
        const editModalBtn = document.querySelectorAll('.edit-scorecard-btn');
        editModalBtn.forEach(button => {
            button.disabled = true;
        });

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/scores/update_user_score.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                editModalBtn.forEach(button => {
                    button.disabled = false;
                });
                window.location.reload();
            },
            error: function(error) {
                console.log(error);
                gimmeToast("Error fetching user scores",'error');
            }  
        });
    }
    function clearScores(){
        const scoreCardInputs = document.querySelectorAll('.scorecard-input');
        for (let i = 0; i < scoreCardInputs.length; i++) {
            scoreCardInputs[i].value = '';
        }
    }
    function clearEditModal() {
        document.querySelector('#event-scoring-first-name').value = "";
        document.querySelector('#event-scoring-last-name').value = "";
        document.querySelector('#event-scoring-email-address').value = "";
        document.querySelector('#handicap-manual').value = "";
        document.querySelector('#event-scoring-gender').value = "";
        document.querySelector('#event-scoring-default-tee').value = "";
        document.querySelector('#event-scoring-phone-number').value = "";
    }
    function disableFields() {
        document.querySelector('#event-scoring-email-address').disabled = true;
        $('#delete-invitation-btn').show(200);
        document.querySelector('#delete-invitation-btn').disabled = false;
    }
    function enableFields() {
        document.querySelector('#event-scoring-email-address').disabled = false;
        $('#delete-invitation-btn').hide(200);
        document.querySelector('#delete-invitation-btn').disabled = true;
    }

    // --------------------------------------------------------------------------------------------------------------------
    // Events
    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
    document.getElementById("scoring-settings-btn").addEventListener("click", function() {
        scoringModal.show(200);
        initializeScoreSettings();
    });
    <?php endif; ?>
    document.querySelector("#enable-custom-settings").addEventListener("click", function(event){
        if(event.target.checked){
            $('#scoring-modal-settings--custom-settings').show(500);
        }else{
            $('#scoring-modal-settings--custom-settings').hide(500);
        }
    });
    document.getElementById("scoring-dismiss-modal-btn").addEventListener("click", function() {
        console.log("Hide modal");
        scoringModal.hide(200);
    });
    document.getElementById("scorecard-dismiss-modal-btn").addEventListener("click", function() {
        console.log("Hide modal");
        scoreCardModal.hide(200);
        clearScores();
    });
    document.querySelector('#save-scorecard-settings-btn').addEventListener("click",function(){
        console.log("Saving scorecard")
        saveScoreCard();
    });
    document.querySelector('#scorecard-clear-btn').addEventListener("click",function(){
        clearScores();
    });
    document.querySelector('#scorecard-withdraw-btn').addEventListener("click",function(){
        disqualifyPlayer()
    });
    document.getElementById("save-scoring-settings-btn").addEventListener("click", function() {
        saveSettingsScoring();
    });
    var inputs = document.querySelectorAll('.scorecard-input');
    for (let i = 0; i < inputs.length; i++) {
        console.log('input detected');
        let newTotal = 0;
        inputs[i].addEventListener('change', function(event) {
            newTotal += parseInt(event.target.value, 10);
        });
        document.querySelector('.total-shots').innerHTML = newTotal.toString();
    }

</script>