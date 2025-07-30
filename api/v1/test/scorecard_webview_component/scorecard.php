<?php
    require_once '../../../Tools/Supabase/vendor/autoload.php';
    require_once 'util.php';
    $config = require_once '../../../Tools/Supabase/config.php';

    // Display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Only allow JSON input
    $_POST = json_decode(file_get_contents('php://input'), true);

    if(!$_GET) {
        http_response_code(400);
        echo json_encode(array("msg" => "Missing required parameters"));
        exit;
    }

    $supabase = new PHPSupabase\Service(
        $config['supabaseKey'],
        $config['supabaseUrl']
    );

    error_reporting(E_ALL & ~E_WARNING);
    $match_db = $supabase->initializeDatabase('gimme_match', 'id');
    $courses_db = $supabase->initializeDatabase('gimme_courses', 'id');
    $event_db = $supabase->initializeDatabase('gimme_events', 'id');
    $scores_db = $supabase->initializeDatabase('gimme_scores', 'id');
    $gimme_users = $supabase->initializeDatabase('gimme_users', 'id');
    $gimme_event_invitations = $supabase->initializeDatabase('gimme_event_invitations', 'id');
    $gimme_user_preferences = $supabase->initializeDatabase('gimme_user_preferences', 'id');

    // Get the parameters from the request
    $game_type = $_GET['game_type']; // Stableford, Strokeplay, etc
    $game_format = isset($_GET['game_format'])? $_GET['game_format'] : null; // Single, Double, etc
    $gamemode = $_GET['gamemode']; // Event or Match
    $game_id = $_GET['game_id']; // This is the ID of the event or the match

    $isTeam = false;

    if($gamemode == 'event'){
        if(isset($_GET['match_or_event_code'])){
            $query = $supabase->initializeQueryBuilder();
            $get_game_id = $query->select('*')
                ->from('gimme_events')
                ->where('event_code', 'eq.'.$_GET['match_or_event_code'])
                ->order('id.desc')
                ->execute()
                ->getResult();
            $game_id = $get_game_id[0]->id;
        }

        // Run DB queries
        $eventObject = $event_db->findBy('id',$game_id)->getResult()[0];
        $scoreObject = $scores_db->findBy('match_type_id',$game_id)->getResult();

        $handicaps = isset($_GET['handicaps']) ? $_GET['handicaps'] : $eventObject->event_handicap; // Gross or Net

        // For markup insertion
        $data = [];

        // Find and append user name onto scoreObject
        $updated_scoreObject = [];
        foreach ($scoreObject as $user_data) {
            $queryy = $supabase->initializeQueryBuilder();
            $user_info = $queryy->select('name', 'surname')
                ->from('gimme_users')
                ->where('id', 'eq.'.$user_data->user_id)
                ->execute()
                ->getResult(); 
            $user_data->name = $user_info[0]->name;

            $user_preferences = $gimme_user_preferences->findBy('uid', $user_data->user_id)->getResult()[0];

            $user_data->name = $user_info[0]->name;
            $user_data->handicap = $user_preferences->handicap; // Get from invitations

            $updated_scoreObject[] = $user_data;
        }

        // Check game types
        if($game_type == 'Strokeplay' || $game_type == 'strokeplay'){
            if($handicaps == 'gross'){
                // Only run teams check if event type is teams
                if($eventObject->event_type == 'team'){
                    $isTeam = true;
                    $data = json_decode(runGrossCalculation_Teams($updated_scoreObject,$eventObject));
                    
                    // TEST
                    // $data = runGrossCalculation_Teams($updated_scoreObject,$eventObject);
                    // header('Content-Type: application/json');
                    // echo json_encode($data);
                    // exit;
                }else{
                    $data = json_decode(runGrossCalculation($updated_scoreObject,$eventObject));

                    // TEST
                    // $data = runGrossCalculation($updated_scoreObject,$eventObject);
                    // header('Content-Type: application/json');
                    // echo json_encode($data);
                    // exit;
                }
            }else{
                if($eventObject->event_type == 'team'){
                    $isTeam = true;
                    $data = json_decode(runNetCalculation_Teams($updated_scoreObject,$eventObject));

                    // TEST
                    // $data = runNetCalculation_Teams($updated_scoreObject,$eventObject);
                    // header('Content-Type: application/json');
                    // echo json_encode($data);
                    // exit;
                }else{
                    $data = json_decode(runNetCalculation($updated_scoreObject,$eventObject));
                }
            }
        }
        if($game_type == 'stableford'){
            if($eventObject->event_type == 'team'){
                $isTeam = true;
                $data = json_decode(runStablefordCalculation_Teams($updated_scoreObject,$eventObject));

                // TEST
                // $data = runStablefordCalculation_Teams($updated_scoreObject,$eventObject);
                // header('Content-Type: application/json');
                // echo json_encode($data);
                // exit;
            }else{
                $data = json_decode(runStablefordCalculation($updated_scoreObject,$eventObject));

                // TEST
                // $data = runStablefordCalculation($updated_scoreObject,$eventObject);
                // header('Content-Type: application/json');
                // echo json_encode($data);
                // exit;
            }
        }
    }else{
        if(isset($_GET['match_or_event_code'])){
            $query = $supabase->initializeQueryBuilder();
            $get_game_id = $query->select('*')
                ->from('gimme_match')
                ->where('match_code', 'eq.'.$_GET['match_or_event_code'])
                ->order('id.desc')
                ->execute()
                ->getResult();        
            $game_id = $get_game_id[0]->id;
        }

        // Run DB queries
        $matchObject = $match_db->findBy('id',$game_id)->getResult()[0];
        $courseObject = $courses_db->findBy('id',$matchObject->course_id)->getResult()[0];
        $scoreObject = $scores_db->findBy('match_type_id',$game_id)->getResult();
        $handicaps = isset($_GET['handicaps']) ? $_GET['handicaps'] : $matchObject->match_format; // Gross or Net
        
        // For markup insertion
        $data = [];

        // Find and append user name onto scoreObject
        $updated_scoreObject = [];
        foreach ($scoreObject as $user_data) {
            $queryy = $supabase->initializeQueryBuilder();
            $user_info = $queryy->select('name', 'surname')
                ->from('gimme_users')
                ->where('id', 'eq.'.$user_data->user_id)
                ->execute()
                ->getResult(); 

            $user_preferences = $gimme_user_preferences->findBy('uid', $user_data->user_id)->getResult()[0];

            $user_data->name = $user_info[0]->name;
            $user_data->handicap = $user_preferences->handicap;

            $updated_scoreObject[] = $user_data;
        }

        if($game_type == 'Strokeplay' || $game_type == 'strokeplay'){
            if($handicaps == 'Gross'){
                // Only run teams check if match type is teams
                if($matchObject->type == 'team'){
                    $isTeam = true;
                    
                    $data = json_decode(runGrossCalculation_Teams($updated_scoreObject,$matchObject,true,$courseObject));

                    // TEST
                    // $data = runGrossCalculation_Teams($updated_scoreObject,$matchObject,true,$courseObject);
                    // echo json_encode($data);
                    // exit;

                }else{
                    $data = json_decode(runGrossCalculation($updated_scoreObject,$matchObject,true,$courseObject));
                }
            }else{
                $data = json_decode(runNetCalculation($scoreObject,$matchObject));
            }
        }

        if($game_type ==  'Stableford' || $game_type == 'stableford'){

            if($matchObject->type == 'team'){
                $isTeam = true;
                $data = json_decode(runStablefordCalculation_Teams($updated_scoreObject,$matchObject,true,$courseObject));
            }else{
                $data = json_decode(runStablefordCalculation($updated_scoreObject,$matchObject,true,$courseObject));
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Scorecard</title>
    <style>
        table {
            font-family: Arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            text-align: left;
            padding: 15px;
            background:#fff;
            font-size:13px;
        }

        table th:not(:first-child),
        table td:not(:first-child) {
            text-align: center;
        }

        table th:first-child,
        table td:first-child {
            white-space: nowrap;
        }

        table th {
            background-color: #fff;
        }

        .scrollable {
            max-height: 300px;
            overflow-y: hidden;
        }

        .fixed {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
        }

        table td:first-child{
            z-index: 99;
        }

        *{
            font-family: "Arial";
        }
        #game-info{
            color: #344767
        }
        </style>
</head>
<body>
    <div id="game-info">
        <h2 style="color:#344767"><?= $gamemode == 'match' ? 'Match' : $eventObject->event_name ?></h2>
        <div id="game-details">
            <span>
                <?= ucwords(strtolower($handicaps))?>
            </span>
            -
            <span>
                <?= ucwords(strtolower($game_type)) ?>
            </span>

            <!-- ONLY FOR TEAMS -->
            <?php if($isTeam && $gamemode == 'event'):?>
                - <span>
                    <?php
                        if($eventObject->event_team_format == "none"){
                            echo "Best Ball";
                        }else{
                            echo ucwords(strtolower($eventObject->event_team_format));
                        }
                    ?>
                </span>
            <?php elseif($isTeam && $gamemode == 'event'): ?>
                - <span>
                    <?php
                        if($matchObject->team_format == "none"){
                            echo "Best Ball";
                        }else{
                            echo ucwords(strtolower($matchObject->team_format));
                        }
                    ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <table>
        <?php 
        if(!$isTeam): 
        ?>
        <th>Pos</th>
        <th>Player Name</th>
        <th style="background-color:#2bb13f;color:white">Score</th>
        <th>Thru</th>
        <th>Total</th>
        <?php
            // Add table data
            // Add table data for Strokeplay
            if($game_type == "strokeplay" || $game_type == "Strokeplay"):
                foreach ($data as $player_data) {
                    echo "<tr>";
                    echo "<td>" . $player_data->pos . "</td>";
                    echo "<td>" . $player_data->name . "</td>";
                    echo "<td class='score' style='background:#2bb13f; color:white;font-weight:bold;'>" . ($player_data->score == 0 ? "E" : $player_data->score) . "</td>";
                    echo "<td>" . $player_data->thru . "</td>";
                    echo "<td>" . $player_data->total . "</td>";
                    echo "</tr>";
                }
            endif;

            // Add table data for Stableford
            if($game_type == "stableford" || $game_type == "Stableford"):
                echo "<th>PTS</th>"; // Points
                foreach ($data as $player_data) {
                    echo "<tr>";
                    echo "<td>" . $player_data->pos . "</td>";
                    echo "<td>" . $player_data->name . "</td>";
                    echo "<td class='score' style='background:#2bb13f; color:white;font-weight:bold;'>" . ($player_data->score == 0 ? "E" : $player_data->score) . "</td>";
                    echo "<td>" . $player_data->thru . "</td>";
                    echo "<td>" . $player_data->total . "</td>";
                    echo "<td>" . $player_data->points . "</td>";
                    echo "</tr>";
                }
            endif;

        elseif ($isTeam):
        ?>
        <th>Pos</th>
        <th>Team Name</th>
        <th>Team</th>
        <th style="background-color:#2bb13f;color:white">Score</th>
        <th>Thru</th>
        <th>Total</th>
        <?php
        
            // Add table data for Strokeplay
            if($game_type == "strokeplay" || $game_type == "Strokeplay"):
                // Add team table data
                foreach ($data as $team => $team_data) {
                    echo "<tr>";
                    echo "<td>" . $team_data->pos . "</td>";
                    echo "<td>" . $team_data->team_name . "</td>";

                    echo "<td>";
                    foreach ($team_data->players as $player) {
                        echo <<<EOD
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            </svg>
                            $player
                        </span>
                        EOD;
                    }
                    echo "</td>";

                    echo "<td class='score' style='background:#2bb13f; color:white;font-weight:bold;'>" . ($team_data->score == 0 ? "E" : $team_data->score) . "</td>";
                    echo "<td>" . $team_data->thru . "</td>";
                    echo "<td>" . $team_data->team_total . "</td>";
                    echo "</tr>";
                }
            endif;

            // Add table data for Strokeplay
            if($game_type == "stableford" || $game_type == "Stableford"):
                echo "<th>PTS</th>"; // Points
                // Add team table data
                foreach ($data as $team => $team_data) {
                    echo "<tr>";
                    echo "<td>" . $team_data->pos . "</td>";
                    echo "<td>" . $team_data->team_name . "</td>";

                    echo "<td>";
                    foreach ($team_data->players as $player) {
                        echo <<<EOD
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            </svg>
                            $player
                        </span>
                        EOD;
                    }
                    echo "</td>";

                    echo "<td class='score' style='background:#2bb13f; color:white;font-weight:bold;'>" . ($team_data->score == 0 ? "E" : $team_data->score) . "</td>";
                    echo "<td>" . $team_data->thru . "</td>";
                    echo "<td>" . $team_data->total . "</td>";
                    echo "<td>" . $team_data->points . "</td>";
                    echo "</tr>";
                }
            endif;

        endif;
        ?>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const headers = document.querySelectorAll('table th, table td');
            const firstColumn = document.querySelector('table th:first-child');
            const lastColumn = document.querySelector('table th:last-child');

            headers.forEach(header => {
                if (header !== firstColumn && header !== lastColumn) {
                    header.setAttribute('draggable', 'true');
                    header.addEventListener('dragstart', function(event) {
                        event.dataTransfer.setData('text/plain', event.target.id);
                    });
                }
            });

            firstColumn.style.position = 'sticky';
            firstColumn.style.left = '0';
            firstColumn.style.background = 'white';
            firstColumn.style.zIndex = '1';

            lastColumn.style.position = 'sticky';
            lastColumn.style.right = '0';
            lastColumn.style.background = 'white';
            lastColumn.style.zIndex = '1';

            const rows = document.querySelectorAll('table tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (index !== 0 && index !== 20) {
                        cell.setAttribute('draggable', 'true');
                        cell.addEventListener('dragstart', function(event) {
                            event.dataTransfer.setData('text/plain', event.target.id);
                        });
                    }
                    if(cell.classList.contains('score')){
                        cell.style.position ='sticky';
                        cell.style.left = '0';
                        cell.style.right = '0';
                        cell.style.background = '#2bb13f';
                        cell.style.color = 'white';
                        cell.style.zIndex = '99!important';
                    }else{
                        cell.style.position = 'sticky';
                        cell.style.left = '0';
                        cell.style.right = '0';
                        cell.style.background = 'white';
                        cell.style.zIndex = '99!important';
                    }
                });
            });
        });
    </script>
    </body>
</html>