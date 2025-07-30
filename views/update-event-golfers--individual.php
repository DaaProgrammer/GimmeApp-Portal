<div id="event-golfers--individual" style="display:none">
    <div id="event-golfers" class="w-100 px-6">
        <div id="event-golfers-table" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden; margin-top: 30px;"></div>
        <div class="actions">
            <?php if ($_SESSION['USER_ROLE'] == "event_organiser"): ?>
            <button class="w2ui-btn w2ui-btn-blue" id="createModalBtn">
                + Add Golfer
            </button>
            <?php endif;?>
        </div>
    </div>

    <div class="modal" id="golfers-modal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Golfer</h5>
                </div>
                <div class="modal-body">
                    <div class="form">
                        <input type="hidden" id="form-type"/>
                        <input type="hidden" name="invitations-list" id="invitations-list" value='<?= json_encode($invitationList->invitation_details)?>'>
                        <div class="form-input">
                            <label for="event-golfers-first-name">First Name</label>
                            <input class="w2ui-input" type="text" name="event-golfers-first-name" id="event-golfers-first-name" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                        </div>

                        <div class="form-input">
                            <label for="event-golfers-last-name">Last Name</label>
                            <input class="w2ui-input" type="text" name="event-golfers-last-name" id="event-golfers-last-name" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                        </div>

                        <div class="form-input">
                            <label for="event-golfers-email-address">Email Address</label>
                            <input class="w2ui-input" type="email" name="event-golfers-email-address" id="event-golfers-email-address" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                        </div>

                        <div class="form-input">
                            <label for="event-golfers-handicap">Handicap</label>
                            <input class="w2ui-input" name="event-golfers-handicap" id="event-golfers-handicap" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                            <div id="handicap-manual-container" style="display:none;border-bottom: 1px solid grey;padding-bottom: 10px;">
                                <label>Handicap Value</label><br/>
                                <input type="number" title="Enter a value between 0 and 100" id="handicap-manual" class="w2ui-input" min="0" max="100">
                            </div>
                        </div>

                        <div class="form-input">
                            <label for="event-golfers-gender">Gender</label>
                            <input class="w2ui-input" name="event-golfers-gender" id="event-golfers-gender" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                        </div>

                        <div class="form-input">
                            <label for="event-golfers-default-tee">Default Tee</label>
                            <input class="w2ui-input" name="event-golfers-default-tee" id="event-golfers-default-tee" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                        </div>

                        <div class="form-input">
                            <label for="event-golfers-phone-number">Phone Number</label>
                            <input class="w2ui-input" name="event-golfers-phone-number" type="number" min="7" max="15" id="event-golfers-phone-number"  <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="golfers-dismiss-modal-btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" style="display:none" class="btn btn-danger" id="delete-invitation-btn" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>Delete Invitation</button>
                    <button type="button" class="btn btn-primary" id="save-golfer-settings-btn" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?>>Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->
</div>

    <script type="module">

        // Loader Container
        const golfersLoaderContainer = document.getElementById('golfers-loaderContainer');
        golfersLoaderContainer.style.display = 'none';

        // Modal
        const golfersModal = $('#golfers-modal');

        // --------------------------------------------------------------------------------------------------------------------
        // Table
        window.initializeGolfersTable = function() {
            window.grid = new w2grid({
                name: 'event-golfers-table',
                box: '#event-golfers-table',
                header: 'Event Golfers',
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
                    { field: 'tee', text: 'Tee', sortable: true },
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    { field: 'action', text: 'Action'}
                    <?php endif; ?>
                ],
                searches: [
                    { field: 'index', text: 'Index', sortable: true },
                    { field: 'name', text: 'Name', sortable: true },
                    { field: 'email', text: 'Email', sortable: true }, 
                    { field: 'tee', text: 'Tee', sortable: true },
                ],
                onExpand(event) {
                    query('#'+event.detail.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>')
                },
                onClick(event){
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    const rowClicked = event.detail.originalEvent.delegate;
                    console.log(JSON.parse(rowClicked.querySelector("td input").getAttribute('data-invite')))

                    // Update form type to allow for changes to be saved
                    document.querySelector('#form-type').value = "update";
                    document.querySelector('#save-golfer-settings-btn').innerHTML = "Update Golfer";

                    // Populate modal
                    prePopulateEditModal(rowClicked.querySelector("td input").getAttribute('data-invite'))

                    // Disable email input
                    disableFields();

                    console.log("Edit modal");
                    golfersModal.show(200);
                    <?php endif; ?>
                }
            });
        }

        // --------------------------------------------------------------------------------------------------------------------
        window.populateGolfersTable = function() {
            // Get users from invitations
            const evenInvitations = <?= json_encode($invitationList)?>;

            console.log(evenInvitations);

            // Process and add new records
            evenInvitations.invitation_details.forEach(function(invite,index) {
                window.grid.add({
                    recid: index,
                    index: index+1,
                    name: invite.name,
                    email: invite.email ? invite.email : "None",
                    tee: invite.tee ? invite.tee : "None",
                    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                    action: `
                        <input type="button" class="w2ui-btn w2ui-btn-blue editModalBtn" value="Edit" data-invite='${JSON.stringify(invite)}' />
                    `
                    <?php endif;?>
                });
            });
        }

        // --------------------------------------------------------------------------------------------------------------------
        // Instantiate W2UI elements here
        new w2field('list', {
            el: document.querySelector("#event-golfers-handicap"),
            items: ['Manual'],
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                console.log('Selected:', event.detail.item)
                $('#handicap-manual-container').show(200);
            }
        });
        new w2field('list', {
            el: document.querySelector("#event-golfers-gender"),
            items: ['Male','Female'],
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                console.log('Selected:', event.detail.item)
            }
        })
        
        let defaultTees = Object.keys(<?php echo json_encode($teeData->tees);?>).map(tee => ({text: tee}));
        // Object.keys(event.detail.item.tee_data.tees).map(tee => ({text: tee}));
        console.log(defaultTees);

        new w2field('list', {
            el: document.querySelector("#event-golfers-default-tee"),
            items: defaultTees,
            match: 'contains',
            markSearch: true,
            onSelect(event) {
                console.log('Selected:', event.detail.item)
            }
        })

        function saveSettingsGolfers(isUpdate = false) {
            const firstName = document.querySelector('#event-golfers-first-name').value;
            const lastName = document.querySelector('#event-golfers-last-name').value;
            const email = document.querySelector('#event-golfers-email-address').value;
            const handicap = document.querySelector('#handicap-manual').value;
            const gender = document.querySelector('#event-golfers-gender').value; 
            const tee = document.querySelector('#event-golfers-default-tee').value;
            const phone = document.querySelector('#event-golfers-phone-number').value;
            // const emailStatus = document.querySelector('.form select[name="email_status"]').value;

            // Validation on required fields
            if(firstName == '') {
                gimmeToast('First name is required','error');
                return false;
            }
            if(lastName == '') {
                gimmeToast('Last name is required','error');
                return false;  
            }
            if(email == '') {
                gimmeToast('Email is required','error');
                return false;
            }
            if(handicap == '') {
                gimmeToast('Handicap is required','error');
                return false;
            }
            if(gender == '') {
                gimmeToast('Gender is required','error');
                return false;
            }
            if(tee == '') {
                gimmeToast('Tee is required','error');
                return false;
            }

            const invitation = {
                name: `${firstName} ${lastName}`,
                email:email,
                handicap:handicap,
                gender:gender,
                tee:tee,
                phone:phone,
                match_type:"<?= $currentEvent->event_type ?>"
            }

            const data = {
                token:TOKEN,
                event_id: <?= $_POST['event_id'] ?>,
                invitation:invitation
            };

            console.log(data);

            // Send AJAX post request to update_event.php
            $.ajax({
                url: !isUpdate ? API_URL + 'api/v1/invitations/add_invitation.php' : API_URL + 'api/v1/invitations/update_invitation.php',
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
                }  
            });
        }

        // --------------------------------------------------------------------------------------------------------------------
        // Edit

        function prePopulateEditModal(invite) {
            console.log(invite);

            // Make invite object usable
            invite = JSON.parse(invite);

            document.querySelector('#event-golfers-first-name').value = invite.name ? invite.name.split(' ')[0] : "None";
            document.querySelector('#event-golfers-last-name').value = invite.name ? invite.name.split(' ')[1] : "None";
            document.querySelector('#event-golfers-email-address').value = invite.email;
            document.querySelector('#handicap-manual').value = invite.handicap;
            document.querySelector('#event-golfers-gender').value = invite.gender;
            document.querySelector('#event-golfers-default-tee').value = invite.tee;
            document.querySelector('#event-golfers-phone-number').value = invite.phone;
        }
        function clearEditModal() {
            document.querySelector('#event-golfers-first-name').value = "";
            document.querySelector('#event-golfers-last-name').value = "";
            document.querySelector('#event-golfers-email-address').value = "";
            document.querySelector('#handicap-manual').value = "";
            document.querySelector('#event-golfers-gender').value = "";
            document.querySelector('#event-golfers-default-tee').value = "";
            document.querySelector('#event-golfers-phone-number').value = "";
        }
        function disableFields() {
            document.querySelector('#event-golfers-email-address').disabled = true;
            $('#delete-invitation-btn').show(200);
            document.querySelector('#delete-invitation-btn').disabled = false;
        }
        function enableFields() {
            document.querySelector('#event-golfers-email-address').disabled = false;
            $('#delete-invitation-btn').hide(200);
            document.querySelector('#delete-invitation-btn').disabled = true;
        }

        // --------------------------------------------------------------------------------------------------------------------
        // Delete
        function deleteInvite(){
            const email = document.querySelector('#event-golfers-email-address').value;
            const formType = document.querySelector('#form-type').value = "update";

            // Ensure the form type is update before attempting to delete
            if(formType != 'update') return false;

            // Validation on required fields
            if(email == '') {
                gimmeToast('Could not extract email to delete','error');
                return false;
            }

            const data = {
                token:TOKEN,
                event_id: <?= $_POST['event_id'] ?>,
                email:email,
                // DEV TEST ONLY
                // inviter_id:"33"
            };
            console.log(data);

            // Send AJAX post request to update_event.php
            $.ajax({
                url: API_URL + 'api/v1/invitations/delete_invitation.php',
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
                    console.log(error.error);
                    gimmeToast("Error deleting invitation",'error');
                }  
            });
        }

        // Events
        document.getElementById("handicap-manual").onkeyup = function() {
            if (this.value < 0 || this.value > 100) {
                this.style.border = "1px solid red";
            } else {
                this.style.border = "";
            }
        }
        <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
        document.getElementById("createModalBtn").addEventListener("click", function() {
            console.log("Show modal");
            // Update form type and button text
            document.querySelector('#form-type').value = "create";
            document.querySelector('#save-golfer-settings-btn').innerHTML = "Add Golfer";
            // Clear any pre-populated values
            clearEditModal();
            // Re-enable fields
            enableFields();
            golfersModal.show(200);  
        });
        <?php endif; ?>
        document.getElementById("golfers-dismiss-modal-btn").addEventListener("click", function() {
            console.log("Hide modal");
            golfersModal.hide(200);
        });
        document.getElementById("golfers-dismiss-modal-btn").addEventListener("click", function() {
            console.log("Hide modal");
            golfersModal.hide(200);
        });
        document.getElementById("save-golfer-settings-btn").addEventListener("click", function() {
            // Determine button action based on the form type [IE- create OR update]
            if(document.querySelector('#form-type').value === "create"){
                saveSettingsGolfers();
            }else{
                saveSettingsGolfers(true);
            }
        });
        document.getElementById("delete-invitation-btn").addEventListener("click",function(){
            deleteInvite();
        });

    </script>