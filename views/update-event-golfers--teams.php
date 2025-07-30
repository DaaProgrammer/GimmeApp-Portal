<div id="event-golfers--teams" style="display:none">
    <div id="event-teams" class="w-100 px-6">
        <div id="event-teams-table" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden; margin-top: 30px;"></div>
        <div class="actions">
            <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
            <button class="w2ui-btn w2ui-btn-blue" id="addTeamModalBtn">
                + Add Team
            </button>
            <?php endif;?>
        </div>
    </div>

    <div class="modal" id="teams-modal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="team-modal-title">Add Team</h5>
                </div>
                <div class="modal-body">
                    <div class="form">
                        <input type="hidden" id="teams-form-type"/>
                        <div class="form-input">
                            <label for="event-teams-name">Team Name</label>
                            <input class="w2ui-input" type="text" name="event-teams-name" id="event-teams-name" placeholder="EG. Fairway Flyers">
                        </div>

                        <div class="form-input">
                            <button class="btn btn-primary" style="width:fit-content" id="add-new-team-member-button">Add New Team Member</button>
                        </div>
                        <div class="form-input" id="new-member-form-input" style="display:none">
                            <label for="event-teams-members">Select a new member</label>
                            <input class="w2ui-input" type="text" name="event-teams-members" id="event-teams-members">
                        </div>

                        <div class="form-input" id="team-members-list--container"  style="display:none">
                            <label for="team-members-list">Team Members</label>
                            <ol id="team-members-list">
                                
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="teams-dismiss-modal-btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" style="display:none" class="btn btn-danger" id="delete-invitation-btn">Delete Team</button>
                    <button type="button" class="btn btn-primary" id="save-team-settings-btn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <script type="module">

        // Delcarations
        const teamsModal = $('#teams-modal');

        // Table
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
        window.initializeTeamsTable = function() {
            window.teamsGrid = new w2grid({
                name: 'event-teams-table',
                box: '#event-teams-table',
                header: 'Event Teams',
                reorderRows: false,
                show: {
                    header: true,
                    footer: true,
                    toolbar: true,
                    lineNumbers: true
                },
                columns: [
                    { field: 'index', text: 'Index', sortable: true },
                    { field: 'team', text: 'Team', sortable: true },
                    { field: 'participants', text: 'Participants', sortable: true },
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    { field: 'action', text: 'Action'}
                    <?php endif;?>
                ],
                searches: [
                    { field: 'index', text: 'Index', sortable: true },
                    { field: 'team', text: 'Team', sortable: true },
                    { field: 'participants', text: 'Participants', sortable: true },
                ],
                onExpand(event) {
                    query('#'+event.detail.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>')
                },
                onClick(event){
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    // Clear any old data from the form
                    clearEditModal();

                    const rowClicked = event.detail.originalEvent.delegate;
                    const teamIndex = rowClicked.querySelector("td input").getAttribute("data-team-index");

                    console.log(rowClicked.querySelector("td input").getAttribute("data-team-index"))

                    // Update form type to allow for changes to be saved
                    document.querySelector('#teams-form-type').value = "update";
                    document.querySelector('#team-modal-title').innerHTML = "Update Team"
                    document.querySelector('#save-team-settings-btn').innerHTML = "Update Golfer";

                    // Add event listener to update team
                    document.querySelector('#save-team-settings-btn').addEventListener('click',function(event){
                        updateSettingsTeams(teamIndex);
                    });

                    // Populate modal
                    prePopulateEditModal(rowClicked.querySelector("td input").getAttribute('data-team'))

                    // Add event listener to remove buttons
                    addEventListenerToRemoveButtons()

                    // Check if members can be added
                    const teamMembers = document.querySelectorAll('.team-members-list--item').length; // Get the total number of team members
                    if(teamMembers >= <?= $currentEvent->event_type == "individual" ? 0 : (int)$currentEvent->match_data->settings->max_team_players ?>){
                        // Disable add member button
                        document.querySelector('#add-new-team-member-button').disabled = true;
                    }else{
                        // Enable add member button
                        document.querySelector('#add-new-team-member-button').disabled = false;
                    }

                    console.log("Edit modal");
                    teamsModal.show(200);
                    <?php endif;?>
                }
            });
        }

        // Populate Teams Table
        window.populateTeamsTable = function() {
            // Get users from invitations
            const eventTeams = <?= $currentEvent->match_data ? json_encode($currentEvent->match_data->teams) : json_encode([]) ?>;

            console.log(eventTeams);

            // Process and add new records
            eventTeams.forEach(function(team,index) {
                window.teamsGrid.add({
                    recid: index,
                    index: index+1,
                    team: team.team_name,
                    participants: team.team_members.length,
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    action: `<input type="button" class="w2ui-btn w2ui-btn-blue editModalBtn" value="Edit" data-team-index="${index}" data-team='${JSON.stringify(team)}'/>`
                    <?php endif;?>
                });
            });
        }

        // Instantiate W2UI elements
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------

        // Get list of available players
        var availablePlayers = <?= json_encode($invitationList->invitation_details)?>;

        availablePlayers = availablePlayers.map((player, index) => {
            if(player.status == "accepted"){
                return {
                    id: player.user_id,
                    text: player.name
                };
            }else{
                return {
                    id: player.user_id,
                    text: player.name + " (Invited)",
                    disabled: true
                };
            }
        });
        console.log('Available Players',availablePlayers);

        const teamMembersSelect = new w2field('list', {
            el: document.querySelector("#event-teams-members"),
            items: availablePlayers,
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                // Set data attr to grab later
                document.querySelector("#event-teams-members").setAttribute('data-value',event.detail.item.id);
                // Hide player selection field
                $('#new-member-form-input').hide();

                // Check if player already exists in the list
                let playerAdded = false;
                const teamMembersList = document.querySelectorAll('.team-members-list--item');
                for (let i = 0; i < teamMembersList.length; i++) {
                    if (teamMembersList[i].getAttribute('data-value') === event.detail.item.id) {
                        playerAdded = true;
                        break;
                    }
                }

                if (!playerAdded) {
                    // Append player to list or team members and show list (if not already shown)
                    $('#team-members-list--container').show()
                    document.querySelector('#team-members-list').innerHTML += `<li class="team-members-list--item" data-value="${event.detail.item.id}"><div style="display:flex;gap:10px"><span style="width:30%;margin-right:10px">${event.detail.item.text}</span><span class="btn btn-danger team-members-list--item---delete">Remove</span></div></li>`;
                    // Update list of selectable players
                    availablePlayers = availablePlayers.map(player => {
                        if (player.id === event.detail.item.id) {
                            return { ...player, disabled: true, text: player.text + ' (Selected)' };
                        }
                        return player;
                    });
                }
            }
        });

        // Create Modal Functions
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------

        // Save Settings for Teams
        function saveSettingsTeams() {

            const teamMembers = [];
            const teamMembersListItems = document.querySelectorAll('.team-members-list--item');
            teamMembersListItems.forEach(item => {
                teamMembers.push(item.getAttribute('data-value'));
            });
            const teamName = document.querySelector('#event-teams-name').value;

            // Validation
            if (!teamName) {
                gimmeToast("Team Name cannot be empty", 'error');
                return;
            }

            const data = {
                token: TOKEN,
                event_id: <?= $_POST['event_id'] ?>,
                team_name: teamName,
                team_members: teamMembers
            };

            // Disable double submissions
            disableFields();
            
            // Send AJAX post request to update_event.php
            $.ajax({
                url: API_URL + 'api/v1/event/add_team.php',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    gimmeToast("Success",'success');
                    setTimeout(function(){
                        window.location.reload();
                    },1000);
                },
                error: function(error) {
                    console.log(error);
                    gimmeToast("Error saving info",'error');
                },
                complete: function(data) {
                    enableFields();
                }
            });
        }

        // Edit Modal Functions
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
        function prePopulateEditModal(team) {

            team = JSON.parse(team);

            const teamName = document.querySelector('#event-teams-name');
            const teamMembers = document.querySelector('#team-members-list');

            teamName.value = team.team_name

                for (let c = 0; c < team.team_members.length; c++){
                    const playerUid = team.team_members[c].uid;
                    const player = availablePlayers.find(player => player.id == playerUid);

                    // TEST
                    console.log('Available Players Array',availablePlayers)
                    console.log('Team Player ID',team.team_members[c]);
                    console.log('Player',player);
                    console.log('Player Text:',player ? "Player Text "+player.text : 'Player Team UID'+team.team_members[c].uid)

                    const playerName = player ? player.text : team.team_members[c].uid;

                    // Code to handle each team object goes here
                    teamMembers.innerHTML += `<li class="team-members-list--item" data-value="${team.team_members[c].uid}"><divstyle="display:flex;gap:10px"><span style="width:30%;margin-right:10px">${playerName}</span><span class="btn btn-dangerteam-members-list--item---delete">Remove</span></div></li>`
                    console.log("Team members")
                }
            $('#team-members-list--container').show(500)
        }

        // Update Settings for Teams
        function updateSettingsTeams(teamIndex) {
            const teamMembers = [];
            const teamMembersListItems = document.querySelectorAll('.team-members-list--item');
            console.log(teamMembersListItems)
            teamMembersListItems.forEach(item => {
                teamMembers.push(item.getAttribute('data-value'));
            });
            const teamName = document.querySelector('#event-teams-name').value;

            // Validation
            if (!teamName) {
                gimmeToast("Team Name cannot be empty", 'error');
                return;
            }

            const data = {
                token: TOKEN,
                event_id: <?= $_POST['event_id'] ?>,
                team_index: teamIndex,
                team_name: teamName,
                team_members: teamMembers
            };

            // Disable double submissions
            disableFields();

            // Send AJAX post request to update_event.php
            $.ajax({
                url: API_URL + 'api/v1/event/update_team.php',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    gimmeToast("Success",'success');
                    setTimeout(function(){
                        window.location.reload();
                    },1000);
                },
                error: function(error) {
                    console.log(error);
                    gimmeToast("Error saving info",'error');
                },
                complete: function(data) {
                    enableFields();
                }
            });
        }
        function clearEditModal() {
            document.querySelector('#event-teams-name').value = '';
            document.querySelector('#team-members-list').innerHTML = '';
        }
        function disableFields() {
            document.querySelector('#save-team-settings-btn').disabled = true;
        }
        function enableFields() {
            document.querySelector('#save-team-settings-btn').disabled = false;
        }

        // Utils
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
        function addEventListenerToRemoveButtons(){
            document.querySelectorAll(".team-members-list--item---delete").forEach(function(element) {
                console.log("Grabbing list item");
                element.addEventListener("click", function(){
                    element.parentNode.remove();
                });

                // Check if members can be added
                const teamMembers = document.querySelectorAll('.team-members-list--item').length; // Get the total number of team members
                if(teamMembers >= <?= $currentEvent->event_type == "individual" ? 0 : (int)$currentEvent->match_data->settings->max_team_players ?>){
                    // Disable add member button
                    document.querySelector('#add-new-team-member-button').disabled = true;
                }else{
                    // Enable add member button
                    document.querySelector('#add-new-team-member-button').disabled = false;
                }
            });
        }

        // Event Listeners
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------
        <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
        document.getElementById("addTeamModalBtn").addEventListener("click", function() {
            console.log("Show modal");
            // Update form type and button text
            document.querySelector('#teams-form-type').value = "create";
            document.querySelector('#team-modal-title').innerHTML = "Add Team"
            document.querySelector('#save-team-settings-btn').innerHTML = "Add Golfer";

            // Add event listener to update team
            document.querySelector('#save-team-settings-btn').addEventListener('click',function(event){
                saveSettingsTeams()
            });

            // Clear any pre-populated values
            clearEditModal();

            // Add event listener to remove buttons
            addEventListenerToRemoveButtons()

            // Re-enable fields
            enableFields();
            teamsModal.show(200);
        });
        <?php endif;?>
        document.getElementById("teams-dismiss-modal-btn").addEventListener("click", function() {
            console.log("Hide modal");
            teamsModal.hide(200);
        });
        document.getElementById("teams-dismiss-modal-btn").addEventListener("click", function() {
            console.log("Hide modal");
            teamsModal.hide(200);
        });
        document.getElementById("add-new-team-member-button").addEventListener("click",function(){
            // Check number of members already added
            // Decide to show or hide selector

            const teamMembers = document.querySelectorAll('.team-members-list--item').length; // Get the total number of team members

            console.log(teamMembers +" - "+ <?= $currentEvent->event_type == "individual" ? 0 : (int)$currentEvent->match_data->settings->max_team_players ?>)

            if(teamMembers >= <?= (int)$currentEvent->match_data->settings->max_team_players ?>){
                console.log(teamMembers+" >= "+<?= (int)$currentEvent->match_data->settings->max_team_players ?>);
                alert("Team is full");
            }else{
                $('#new-member-form-input').toggle(500);
            }
        });

    </script>