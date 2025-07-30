
<?php 
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

    $_POST = json_decode(file_get_contents('php://input'), true);

    require '../auth/token.php';

    // Validate the required fields
    if(!isset($_POST['token'])){
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid request"));
        exit;
    }
 
    $supabase = new PHPSupabase\Service(
        $config['supabaseKey'],
        $config['supabaseUrl']
    );

    // Get the parameters from the request
    $gamemode = $_POST['gamemode'];
    $match_or_event_id = $_POST['match_or_event_id'];
    $selectedround = $_POST['selectedround'];
  


    if($gamemode=='match'){
        $db_gimme_match = $supabase->initializeDatabase('gimme_match', 'id');

    }else{
        $db_gimme_match = $supabase->initializeDatabase('gimme_events', 'id');
    }

    try{
        if($gamemode=='match'){
            $gimme_match_data = $db_gimme_match->findBy('match_code', $match_or_event_id)->getResult();
            $game_id = $gimme_match_data[0]->id;
            $match_settings = $gimme_match_data[0]->match_settings;

        }else{
            $gimme_match_data = $db_gimme_match->findBy('event_code', $match_or_event_id)->getResult();
            $game_id = $gimme_match_data[0]->id;
            $match_settings = $gimme_match_data[0]->match_data;
        }
   
 

        // print_r($match_settings);

        $status = 'complete';
        
        $query = $supabase->initializeQueryBuilder();
        
        try{
            $scores = $query->select('*')
                ->from('gimme_scores')
                // ->where('status', 'eq.'.$status)
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
                        if ($hole->hole_number == $selectedround) {
                            $shot_count = count($hole->shots);
                            $temp_hole_shots_count[] = [
                                'hole_number' => $hole->hole_number,
                                'shots' => count($hole->shots),
                            ];
    
                            $team_name = '';
                            $counter = 0;
                            // print_r($match_settings);

                            foreach ($match_settings->teams as $setting) {
                                foreach ($setting->team_members as $member) {
                                    if ($member->uid == $user_id) {
                                        $team_name = $setting->team_name;
                                    }
                                }
                            }
    
                            $final_hole_shots_count[] = [
                                'user_id' => $user_id,
                                'username' => $username,
                                'team_name' => $team_name,
                                'match_type_id' => $game_id,
                                'hole_shots' => $temp_hole_shots_count,
                                'quick_sc' => $hole->quick_sc
                            ];
                            
                        }
                    }
                    $counter += 1;
                }
                
    
                $unique_teams = array_unique(array_column($final_hole_shots_count, 'team_name'));
                http_response_code(200);
                echo json_encode(array("msg" => "Success", 'data' => $unique_teams));
        }
        catch(Exception $e){
            http_response_code(500);
            echo json_encode(array("msg" => "Something went wrong"));
        }
    }
    catch(Exception $e){
        http_response_code(500);
        echo json_encode(array("msg" => "Something went wrong"));
    }
  

?>
