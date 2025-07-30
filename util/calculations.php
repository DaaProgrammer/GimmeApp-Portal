<?php

function strokePlay_Individual($hole_start,$hole_end,$holes){
    // $holes = $scoresList[0]->score_details->holes;
    for ($i = $hole_start-1; $i < $hole_end; $i++) {
        $shots = 0;
            
        // Admin score overrides user score
        if($holes[$i]->total_shots != 0){
            $shots = $holes[$i]->total_shots;
        }
        // User QUICK_SC score overrides original score
        else if($holes[$i]->quick_sc == "true"){
            $shots = $holes[$i]->quick_putss + $holes[$i]->quick_shots;
        }
        // Original score
        else{
            $shots = count($holes[$i]->shots);
        }
        $total_shots += $shots;

        if($holes[$i]->status == "complete"){
            echo  <<<EOD
                <td>$shots</td>
            EOD;
        }else{
            echo  <<<EOD
                <td>0</td>
            EOD;  
        }
        if ($i+1 == 9 || $i+1 == 18) {
            echo "<td class='scorecard-label-helper total'> $total_shots </td>";
            $total_shots = 0;
        }
    }
}

?>