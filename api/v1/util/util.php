<?php
// display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function generatePassword() {
    $length = 10;
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-=+;:,<.>?';
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

function generateEventCode(){
    $length = 8;
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

function getCurrentTimestampWithTimezone() {
    $timezone = 'America/New_York';
    // Create a new DateTime object with the current time and specified timezone
    $date = new DateTime('now', new DateTimeZone($timezone));

    // Get the timestamp with timezone
    return $date->format('Y-m-d H:i:sP');
}

// Scoring
function extractAndAttachHandicap($scoreObject, $invitations) {
    // Loop through each score object
    foreach($scoreObject as $score) {
        // Loop through each invitation
        foreach($invitations->invitation_details as $invitation) {
            // Check if the user ID matches between the score and invitation
            if ($invitation->user_id == $score->user_id) {
                // If there is a match, assign the handicap value from the invitation to the score
                $score->handicap = (int)$invitation->handicap; 
            }
        }
    }
    return $scoreObject;
}

function calculateNet($gross,$par){
    $net = ($gross - $par) + 1; // On par is equal to 1. IE, gross 4 on par 4 is 1 net.
    return $net;
}

function runNetCalculation($scoreObject,$eventObject){
    $return_data = array();

    foreach($scoreObject as $score){
        $totalNet = 0;
        $totalGross = 0;
        $thru = 0;
        $net = 0;

        $course_hole_data = $eventObject->course_data->course_data->hole_data;
        $score_hole_data = $score->score_details->holes;

        // Loop through hole data to calculate scores
        foreach($course_hole_data as $hole_key => $hole){
            $hole_index = (int)str_replace('hole_','',$hole_key) - 1;

            // Get shots for current hole
            $shots = 0;
            if($score_hole_data[$hole_index]->total_shots > 0) {
                $shots = $score_hole_data[$hole_index]->total_shots;
            }elseif($score_hole_data[$hole_index]->quick_sc == true){
                $shots = $score_hole_data[$hole_index]->quick_putss + $score_hole_data[$hole_index]->quick_shots;
            }else{
                $shots = count($score_hole_data[$hole_index]->shots);
            }

            $net = calculateNet($shots, $hole->par);
            $totalNet += $net;
            $totalGross += $shots;  

            // Increment Thru value as each hole passes
            $thru++;
        }

        array_push(
            $return_data,
            array(
                "user_id" => $score->user_id,                
                "name" => $score->name,
                "net" => $net,
                "total_net" => $totalNet,
                "total_gross" => $totalGross,
                "thru" => $thru
            )
        );
    }

    return json_encode($return_data);
}

function runGrossCalculation($scoreObject,$gameObject,$isMatch=false,$courseObject=false){
    $return_data = array();

    $iterationCounter = 0;
    foreach($scoreObject as $score){
        $iterationCounter++;

        // return json_encode($score);

        $final_score = [];
        $current_score = 0;
        $thru = 0;
        $total = 0;

        $course_hole_data = !$isMatch ? $gameObject->course_data->course_data->hole_data : $courseObject->course_data->course_data->hole_data;
        $score_hole_data = $score->score_details->holes;

        // Loop through hole data to calculate scores
        foreach($course_hole_data as $hole_key => $hole){
            $hole_index = (int)str_replace('hole_','',$hole_key) - 1;

            // Only calculate if hole is complete
            if($score_hole_data[$hole_index]->status == "complete"){

                // Get shots for current hole
                $shots = 0;
                if($score_hole_data[$hole_index]->total_shots > 0) {
                    $shots = $score_hole_data[$hole_index]->total_shots;
                }elseif($score_hole_data[$hole_index]->quick_sc == true){
                    $shots = $score_hole_data[$hole_index]->quick_putss + $score_hole_data[$hole_index]->quick_shots;
                }else{
                    $shots = count($score_hole_data[$hole_index]->shots);
                }

                // echo "Shots: $shots";
                // echo "<br>";
                // echo "Par: {$hole->par}";
                // echo "<br>";
                $current_score = ($shots) - ($hole->par);
                $final_score[] = $current_score;
                $total += $shots;

                // Increment Thru value as each hole passes
                $thru++;
            }
        }

        $final_score = array_sum($final_score);
        //echo json_encode($final_score);

        array_push(
            $return_data,
            array(
                "user_id" => $score->user_id,                
                "name" => $score->name,
                "score" => $final_score,
                "total" => $total,
                "thru" => $thru
            )
        );
    }
    // return json_encode($iterationCounter);

    // Sort return_data array in ascending order by total shots
    usort($return_data, function($a, $b) {
        if ($a['total'] = 0) return 0;
        return $a['total'] - $b['total'];
    });

    // Determine position of each player
    foreach ($return_data as $i => $player) {
        $pos = $i + 1;
        if ($i > 0 && $return_data[$i-1]['score'] == $player['score'] && $player[$i]['thru'] != 0 && $return_data[$i-1]['thru'] != 0) {
            $pos = "T" . $i; 
            // Update the position of the player if they have the same total shots
            $return_data[$i-1]['pos'] = "T" . $i;
        }
        $return_data[$i]['pos'] = $pos;
    }

    return json_encode($return_data);
}

function runGrossCalculation_Teams($scoreObject,$gameObject,$isMatch=false,$courseObject=false){
    $team_data = array();

    // Grab and sort data for each team
    // For every team in the match
    $objectToLoop = $isMatch? $gameObject->match_settings->teams : $gameObject->match_data->teams;
    foreach($objectToLoop as $team_index => $team) {
        // For every player in the team
        foreach($team->team_members as $member_uid) {
            // Find team members score
            $playerScore = null;
            foreach ($scoreObject as $score) {
                if ($score->user_id == $member_uid->uid) {
                    $playerScore = $score->score_details;
                    $playerScore->name = $score->name;
                    break; 
                }
            }
            $team_data[$team->team_name]['team'][$member_uid->uid] = $playerScore;
        }
    }

    // Run calculations for each team
    foreach($team_data as $team_name => $team_players) {
        // Calculate players score
        $final_score = [];
        $current_score = 0;
        $thru = 0;
        $total = 0;

        $course_hole_data = !$isMatch ? $gameObject->course_data->course_data->hole_data : $courseObject->course_data->course_data->hole_data;

        $lowest_number_of_shots=100;
        $last_hole = 0;
        $shots_display = []; //TEST
        foreach ($team_players['team'] as $index => $player) {
            $score_hole_data = $player->holes;
            // Loop through hole data to calculate scores
            foreach($course_hole_data as $hole_key => $hole){
                $hole_index = (int)str_replace('hole_','',$hole_key) - 1;
                // Only calculate if hole is complete
                if($score_hole_data[$hole_index]->status == "complete"){
                    // Get shots for current hole
                    $shots = 0;
                    if($score_hole_data[$hole_index]->total_shots > 0) {
                        $shots = $score_hole_data[$hole_index]->total_shots;
                    }elseif($score_hole_data[$hole_index]->quick_sc == true){
                        $shots = $score_hole_data[$hole_index]->quick_putss + $score_hole_data[$hole_index]->quick_shots;
                    }else{
                        $shots = count($score_hole_data[$hole_index]->shots);
                    }
                    
                    // Only calculate current_score for player with lowest shots
                    $shots_display[] = "$shots < $lowest_number_of_shots && $hole_index != $last_hole - ".($shots < $lowest_number_of_shots);
                    if ($shots < $lowest_number_of_shots && $shots != 0 && $hole_index != $last_hole) {/* if shots is less than lowest_number_of_shots and hole is not the last hole */
                        $current_score += ($shots) - ($hole->par);
                        $lowest_number_of_shots = $shots;
                        $last_hole = $hole_index;

                        // return "$lowest_number_of_shots\n$current_score += ($shots) - ($hole->par)";
                    }

                    $final_score[] = $current_score;
                    
                    $total += $shots; // Total is the combination of all shots for the team

                    // Increment Thru value as each hole passes
                    $thru++;
                }
            }
        }
        // return $shots_display;

        $final_score = array_sum($final_score);
        $team_data[$team_name]['score'] = $final_score;
        //$team_data[$team_name]['score'] = $current_score;
        $team_data[$team_name]['team_total'] = $total;
        $team_data[$team_name]['thru'] = $thru;
        $team_data[$team_name]['team_name'] = $team_name;
    }

    usort($team_data, function($a, $b) {
        if ($a['score'] = 0) return 0;
        return $a['score'] - $b['score'];
    });

    // Determine position of each team
    foreach ($team_data as $i => $team) {
        $pos = $i + 1;
        if ($i > 0 && $team_data[$i-1]['score'] == $team['score'] && $team_data[$i]['thru'] != 0 && $team_data[$i-1]['thru'] != 0) {
            $pos = "T" . $i;
            // Update the position of the team if they have the same score 
            $team_data[$i-1]['pos'] = "T" . $i;
        }
        $team_data[$i]['pos'] = $pos;
    }

    return json_encode($team_data);
}

function runStablefordCalculation($scoreObject, $gameObject, $isMatch=false, $courseObject=false){
    // Initialize variables
    $playerScores = array();
    
    // Loop through each player
    foreach ($scoreObject as $playerId => $playerScore) {
        $playerTotal = 0;
        $thru = 0;
        // Loop through each hole played
        foreach ($playerScore->score_details->holes as $holeId => $holeScore) {
            // Only run calculations if hole is complete
            if($holeScore->status == "complete"){
                // Get hole par
                $hole_key = "hole_".$holeId+1;
                $holePar = !$isMatch ? $gameObject->course_data->course_data->hole_data->$hole_key->par : $courseObject->course_data->course_data->hole_data->$hole_key->par;

                // Calculate gross score for hole
                // Get users shots
                $shots = 0;
                if($holeScore->total_shots > 0) {
                    $shots = $holeScore->total_shots;
                }elseif($holeScore->quick_sc == true){
                    $shots = $holeScore->quick_putss + $holeScore->quick_shots;
                }else{
                    $shots = count($holeScore->shots);
                }

                // Only calculate current_score for player with lowest shots
                $score = $shots - $holePar;
                
                // Award points based on gross score vs par
                if ($score == -4) {
                    $points = 6; // Four under par
                } else if ($score == -3) {
                    $points = 5; // Double Eagle
                } else if ($score == -2) {
                    $points = 4; // Eagle
                } else if ($score == -1) {
                    $points = 3; // Birdie  
                } else if ($score == 0) {
                    $points = 2; // Par
                } else if ($score == 1) {
                    $points = 1; // Bogey
                } else {
                    $points = 0; // Double bogey or worse
                }
                
                // Add points for hole to player's total
                $totalScore += $score;
                $total += $shots;
                $playerPoints += $points;
                $thru++;
            }
        }
        $playerName = $playerScore->name;

        // Add player total to array
        array_push($playerScores, [
            'name' => $playerName,
            'score' => $totalScore,
            'total' => $total,
            'points' => $playerPoints,
            'thru' => $thru
        ]);
    }

    // Determine position of each player
    // Sort the array based on the "total" values
    usort($playerScores, function($a, $b) {
        return $b['points'] - $a['points'];
    });

    // Iterate through the sorted array to set the "pos" key
    foreach ($playerScores as $i => $team) {
        $pos = $i + 1;
        if ($i > 0 && $playerScores[$i-1]['points'] == $team['points'] && $team[$i]['thru'] != 0 && $playerScores[$i-1]['thru'] != 0) {
            $pos = "T" . $i;
            // Update the position of the team if they have the same score 
            $playerScores[$i-1]['pos'] = "T" . $i;
        }
        $playerScores[$i]['pos'] = $pos;
    }
    
    return json_encode($playerScores);
}

function runStablefordCalculation_Teams($scoreObject, $gameObject, $isMatch=false, $courseObject=false){
    // Initialize variables
    $playerScores = array();
    $team_data = array();
    $return_data = array(); // Team data is not saved after looping. Assigning new data does persist and hence, is used.

    // Grab and sort data for each team
    // For every team in the match
    $objectToLoop = $isMatch? $gameObject->match_settings->teams : $gameObject->match_data->teams;
    foreach($objectToLoop as $team_index => $team) {
        // For every player in the team
        foreach($team->team_members as $member_uid) {
            // Find team members score
            $playerScore = null;
            foreach ($scoreObject as $score) {
                if ($score->user_id == $member_uid->uid) {
                    $playerScore = $score->score_details;
                    $playerScore->name = $score->name;
                    $playerScore->uid = $score->user_id;
                    $playerScore->handicap = $score->handicap;
                    break; 
                }
            }
            $team_data[$team->team_name]['team'][$member_uid->uid] = $playerScore; // Add player score to team array
        }
    }
    
    // Loop through each team
    foreach ($team_data as $team_name => $team) {
        $lowestShots = 999; // Start with the highest number of shots
        $lowestPlayerId = null;
        
        // Loop through each player
        foreach ($team['team'] as $playerId => $playerScore) {

            // Append the players name to the return array of that team
            $return_data[$team_name]['players'][] = $playerScore->name;
            
            // Loop through each hole played
            foreach ($playerScore->holes as $holeId => $holeScore) {
                // Only run calculations if hole is complete
                if($holeScore->status == "complete"){
                    
                    // Get user shots
                    $shots = 0;
                    if($holeScore->total_shots > 0) {
                        $shots = $holeScore->total_shots;
                    }elseif($holeScore->quick_sc == true){
                        $shots = $holeScore->quick_putss + $holeScore->quick_shots;
                    }else{
                        $shots = count($holeScore->shots);
                    }
                    
                    // Track lowest shots
                    if ($shots < $lowestShots) {
                        $lowestShots = $shots;
                        $lowestPlayerId = $playerId;
                    }
                }
            }
        
            // Only run point calculations for lowest player
            if ($lowestPlayerId == $playerId) {

                $playerTotal = 0;
                $thru = 0;
                
                // Loop through each hole played
                foreach ($playerScore->holes as $holeId => $holeScore) {
                    // Only run calculations if hole is complete
                    if($holeScore->status == "complete"){
                        // Get hole par
                        $hole_key = "hole_".$holeId+1;
                        $holePar = !$isMatch ? $gameObject->course_data->course_data->hole_data->$hole_key->par : $courseObject->course_data->course_data->hole_data->$hole_key->par;

                        // Calculate gross score for hole
                        // Get users shots
                        $shots = 0;
                        if($holeScore->total_shots > 0) {
                            $shots = $holeScore->total_shots;
                        }elseif($holeScore->quick_sc == true){
                            $shots = $holeScore->quick_putss + $holeScore->quick_shots;
                        }else{
                            $shots = count($holeScore->shots);
                        }

                        // Only calculate current_score for player with lowest shots
                        $score = $shots - $holePar;
                        
                        // Award points based on gross score vs par
                        if ($score == -4) {
                            $points = 6; // Four under par
                        } else if ($score == -3) {
                            $points = 5; // Double Eagle
                        } else if ($score == -2) {
                            $points = 4; // Eagle
                        } else if ($score == -1) {
                            $points = 3; // Birdie  
                        } else if ($score == 0) {
                            $points = 2; // Par
                        } else if ($score == 1) {
                            $points = 1; // Bogey
                        } else {
                            $points = 0; // Double bogey or worse
                        }
                        
                        // Add points for hole to player's total
                        $totalScore += $score;
                        $total += $shots;
                        $playerPoints += $points;
                        $thru++;
                    }
                }
                
                $playerName = $playerScore->name;

                // Add team data to array
                $return_data[$team_name]['score'] += $totalScore;
                $return_data[$team_name]['total'] = $total;
                $return_data[$team_name]['points'] += $playerPoints;
                $return_data[$team_name]['thru'] = $thru;
                $return_data[$team_name]['team_name'] = $team_name; // When sorting the array, this key will keep the team name associated with the data
            }
        }
    }

    // Determine position of each player
    // Sort the array based on the "total" values
    usort($return_data, function($a, $b) {
        return $b['points'] - $a['points'];
    });

    // Iterate through the sorted array to set the "pos" key
    foreach ($return_data as $i => $team) {
        $pos = $i + 1;
        if ($i > 0 && $return_data[$i-1]['points'] == $team['points'] && $team[$i]['thru'] != 0 && $return_data[$i-1]['thru'] != 0) {
            $pos = "T" . $i;
            // Update the position of the team if they have the same score 
            $return_data[$i-1]['pos'] = "T" . $i;
        }
        $return_data[$i]['pos'] = $pos;
    }
    
    return json_encode($return_data);
}

?>