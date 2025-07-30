<div id="settings-golfers" style="display:none">
    <!-- Loader Container -->
    <div id="golfers-loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
        <div class="loader"></div>
    </div>

    <style>
        #custom-tabs{
            width:100%;
            display:flex;
            padding:30px;
        }
        .custom-tab--button{
            width: 50%;
            padding:12px;
            background-color: #00b240;
            text-align:center;
            color:white;
            transition: all 0.5s;
        }
        .custom-tab--button:hover{
            background-color:#53b576;
        }
        .custom-tab--button---selected{
            background-color:#53b576;
        }
    </style>

    <?php if($currentEvent->event_type == "team"):?>
        <div id="custom-tabs">
            <div class="custom-tab--button <?= $currentEvent->event_type == "individual" ? "custom-tab--button---selected" : "" ?>">Individual</div>
            <div class="custom-tab--button <?= $currentEvent->event_type == "team" ? "custom-tab--button---selected" : "" ?>">Teams</div>
        </div>

    <?php endif;?>
    <div id="custom-tabs-view">
        <?php
            // Always call individual tab
            include "update-event-golfers--individual.php";

            // Only call teams tab if event_type is teams
            if($currentEvent->event_type == "team"){
                include "update-event-golfers--teams.php";
            }
        ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const individualTabButton = document.querySelectorAll('.custom-tab--button')[0];
        const teamsTabButton = document.querySelectorAll('.custom-tab--button')[1];
        const individualSection = $('#event-golfers--individual');

        <?php 
        // Tab controls only needed if event_type is teams
        if($currentEvent->event_type == "team"):
        ?>
        const teamsSection = $('#event-golfers--teams');

        // Custom tab controls
        individualTabButton.addEventListener('click', function(event) {
            // Hide/Show selection style
            event.target.classList.add('custom-tab--button---selected');
            teamsTabButton.classList.remove('custom-tab--button---selected');

            // Instantiate table
            try{
                setTimeout(function() {
                    window.initializeGolfersTable();
                    window.populateGolfersTable();
                }, 510);
            }catch(err){
                // console.log(err)
            }

            // Hide/Show section
            teamsSection.hide();
            individualSection.show(500);
        });

        teamsTabButton.addEventListener('click', function(event) {
            event.target.classList.add('custom-tab--button---selected');
            individualTabButton.classList.remove('custom-tab--button---selected');

                    // Instantiate table
                    try{
                        setTimeout(function() {
                            window.initializeTeamsTable();
                            window.populateTeamsTable();
                        }, 1000);
                    }catch(err){
                        console.log("Could not load teams view")
                        console.log(err)
                    }

            individualSection.hide();
            teamsSection.show(500);
        });
        <?php endif;?>

        // Set default tab
        <?php
            if($currentEvent->event_type == "individual"){
                echo <<<EOD
                    // Instantiate table
                    try{
                        setTimeout(function() {
                            window.initializeGolfersTable();
                            window.populateGolfersTable();
                        }, 1500);
                    }catch(err){
                        // console.log(err)
                    }
        
                    // Hide/Show section
                    // teamsSection.hide();
                    individualSection.show(500);
                EOD;
            }else{
                // Something similar to the above
                echo <<<EOD
                    // Instantiate table
                    try{
                        setTimeout(function() {
                            window.initializeTeamsTable();
                            window.populateTeamsTable();
                        }, 1000);
                    }catch(err){
                        console.log("Could not load teams view")
                        console.log(err)
                    }
        
                    // Hide/Show section
                    individualSection.hide();
                    teamsSection.show(500);
                EOD;
            }
        ?>
    });
    </script>

</div>
</div>