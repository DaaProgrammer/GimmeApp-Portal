<?php

$userScore = 5; //GREEN SCORE
$parScore = 4;

$result = '';
$icon = '';

if ($userScore == 1) {
    $result = 'Hole-in-One';
    $icon = 'golf_ball_in_cup';
} elseif ($userScore - $parScore == -4) {
    $result = 'Condor';
    $icon = 'four_birds';
} elseif ($userScore - $parScore == -3) {
    $result = 'Albatross';
    $icon = 'triple_bird';
} elseif ($userScore - $parScore == -2) {
    $result = 'Eagle';
    $icon = 'double_bird';
} elseif ($userScore - $parScore == -1) {
    $result = 'Birdie';
    $icon = 'single_bird';
} elseif ($userScore - $parScore == 0) {
    $result = 'Par';
    $icon = 'score_border';
} elseif ($userScore - $parScore == 1) {
    $result = 'Bogey';
    $icon = 'single_arrow';
} elseif ($userScore - $parScore == 2) {
    $result = 'Double Bogey';
    $icon = 'double_arrow';
} elseif ($userScore - $parScore == 3) {
    $result = 'Triple Bogey';
    $icon = 'triple_arrow';
} else {
    $result = 'Worse than Triple Bogey';
    $icon = 'no_icon';
}

$response = array(
    'user_score' => $userScore,
    'result' => $result,
    'icon' => $icon,
);

echo json_encode($response);

?>
