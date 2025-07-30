
<?php 
require_once '../../Tools/Supabase/vendor/autoload.php';
$config = require_once '../../Tools/Supabase/config.php';
    // display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        // It's a preflight request, respond accordingly
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        exit;
    }
    // header("Access-Control-Allow-Origin: *");
    // header('Content-Type: application/json');

    ?>
<html>
    <head>
        <title>Live Leaderboard</title>
        <style>

        table {
            font-family: Arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            /* border: 1px solid #dddddd; */
            text-align: left;
            padding: 32px;
            background:#fff;
            font-size:63px;
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
        </style>
        </style>
    </head>
    <body>

    <?php 


    $_POST = json_decode(file_get_contents('php://input'), true);

    // require '../auth/token.php';

    // // Validate the required fields
    // if(!isset($_GET['token'])){
    //     http_response_code(400);
    //     echo json_encode(array("msg" => "Invalid request"));
    //     exit;
    // }

    $supabase = new PHPSupabase\Service(
        $config['supabaseKey'],
        $config['supabaseUrl']
    );

    // Get the parameters from the request
    $uid = $_GET['userid'];
    $gamemode = $_GET['gamemode'];
    $match_or_event_id = $_GET['match_or_event_id'];


    // $gamemode = "match";
    // $match_or_event_id = "9e523ff9";


    
    if($gamemode=='match'){
        $db_gimme_match = $supabase->initializeDatabase('gimme_match', 'id');

    }else{
        $db_gimme_match = $supabase->initializeDatabase('gimme_events', 'id');
    }


    try{
        if($gamemode=='match'){
            $gimme_match_data = $db_gimme_match->findBy('match_code', $match_or_event_id)->getResult();
        }else{
            $gimme_match_data = $db_gimme_match->findBy('event_code', $match_or_event_id)->getResult();

        }
   
        $game_id = $gimme_match_data[0]->id;

        // $db_scores = $supabase->initializeDatabase('gimme_scores', 'id');
        // $user_id = 4;
        $status = 'complete';
        // $query_scores = [
        //     'select' => '*',
        //     'from'   => 'gimme_scores',
        //     'where' => 
        //     [
        //         'match_type_id' => 'eq.'.$game_id,
        //         // 'status' => 'eq.'.$status,
        //     ]
        // ];




        $query = $supabase->initializeQueryBuilder();


        
        try{
            // $scores = $db_scores->createCustomQuery($query_scores)->getResult();
            // print_r($scores[0]->score_details->holes);
          
            $scores = $query->select('*')
                    ->from('gimme_scores')
                    ->where('match_type_id', 'eq.'.$game_id)
                    ->order('id.desc')
                    ->execute()
                    ->getResult();

            $hole_shots_count = [];
            $final_hole_shots_count = [];
            foreach ($scores as $score) {
                $temp_hole_shots_count = [];
                $user_id = $score->user_id; // Get the user_id from $scores

                // Retrieve username from gimme_users table using $user_id
                $user = $supabase->initializeDatabase('gimme_users', 'id')->findBy('id', $user_id)->getResult();
                $username = $user[0]->name;

                // Edit: If the $user_id is equal to $uid then change the $username to You
                if ($user_id == $uid) {
                    $username = 'You';
                }

                $user_scores = [];
                $total_score = 0; // Initialize total score variable
                foreach ($score->score_details->holes as $hole) {
                    $shot_count = count($hole->shots);
                    $user_scores[] = $shot_count;
                    $total_score += $shot_count; // Sum up the scores
                }
                $final_hole_shots_count[] = [
                    'user_id' => $user_id,
                    'username' => $username, // Add username to the array
                    'scores' => $user_scores,
                    'total' => $total_score // Add total score to the array
                ];
            }
            
            // print_r($final_hole_shots_count);
        }
        catch(Exception $e){
            echo $e->getMessage();
        }

 
    }
    catch(Exception $e){
        echo $e->getMessage();
    }

?>


        <table>
            <tr>
                <th>Player Name</th>
                <th>H1</th>
                <th>H2</th>
                <th>H3</th>
                <th>H4</th>
                <th>H5</th>
                <th>H6</th>
                <th>H7</th>
                <th>H8</th>
                <th>H9</th>
                <th>H10</th>
                <th>H11</th>
                <th>H12</th>
                <th>H13</th>
                <th>H14</th>
                <th>H15</th>
                <th>H16</th>
                <th>H17</th>
                <th>H18</th>
                <th>Total</th>
            <tr>
            <?php
            foreach ($final_hole_shots_count as $player_data) {
                echo "<tr>";
                echo "<td>" . $player_data['username'] . "</td>";
                foreach ($player_data['scores'] as $score) {
                    echo "<td>" . $score . "</td>";
                }
                echo "<td>" . $player_data['total'] . "</td>";
                echo "</tr>";
            }
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
                    cell.style.position = 'sticky';
                    cell.style.left = '0';
                    cell.style.right = '0';
                    cell.style.background = 'white';
                    cell.style.zIndex = '99!important';
                });
            });
        });
    </script>
    </body>
</html>