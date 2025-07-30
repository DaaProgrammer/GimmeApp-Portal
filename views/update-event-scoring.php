<?php
    $type = $currentEvent->event_type;

    if($type == "individual"){
        include "update-event-scoring--individual.php";
    }else{
        include 'update-event-scoring--teams.php';
    }
?>