<?php 
// display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // It's a preflight request, respond accordingly
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, GET, PUT, DELETE, OPTIONS");
    exit;
}
header("Access-Control-Allow-Origin: *");

require '../auth/token_maps.php';
require_once '../util/util.php';

// Validate the required fields
$token = null;
if(!isset($_GET['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
} else {
    $token = $_GET['token'];
}

$course_id = null;
if(!isset($_GET['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid course id"));
    exit;
} else {
    $course_id = $_GET['course_id'];
}

$user_id = null;
if(!isset($_GET['user_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid user id"));
    exit;
} else {
    $user_id = $_GET['user_id'];
}

$mode = null;
if(!isset($_GET['mode'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid game mode"));
    exit;
} else {
    $mode = $_GET['mode'];
}

$hole = null;
if(!isset($_GET['hole'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid hole data"));
    exit;
} else {
    $hole = $_GET['hole'];
}

$popups = false;
if(!isset($_GET['popups'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid popup value"));
    exit;
} else {
    $popups = $_GET['popups'];
}

$distance_pref = 'yards';
if(!isset($_GET['distance'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid distance value"));
    exit;
} else {
    $distance_pref = $_GET['distance'];
}

$plan = null;
if(isset($_GET['mode'])){
    
    if($_GET['mode'] == 'plan'){
        $plan = $_GET['mode'];
    } elseif($_GET['mode'] == 'event'){
        $plan = $_GET['mode'];
    } elseif($_GET['mode'] == 'match'){
        $plan = $_GET['mode'];
    } else {
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid game mode"));
        exit;
    }

}

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$load_map = false;
$courses_db = $supabase->initializeDatabase('gimme_courses','id');

try {
    $query = [
       'select' => "*",
        'from' => "gimme_courses",
        'where' => [
            'id' => 'eq.'.$_GET['course_id']
        ]
    ];

    $courses = $courses_db->createCustomQuery($query)->getResult();

    if(empty($courses)){
        http_response_code(400);
        echo json_encode(array("msg" => "No data found"));
        exit;
    }

    $course = $courses[0];
    // get the location_gps from the course record
    $location_gps = $course->location_gps;
    $course_data = $course->course_data;
    $location_gps = json_encode($location_gps);
    $course_data = json_encode($course_data);

    $load_map = true;

    if($plan == 'plan'){
        // create a supabase query to gimme_plan_game and retrieve the game_data json object
        $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
        $query = [
           'select' => "*",
            'from' => "gimme_plan_game",
            'where' => [
                'user_id' => 'eq.'.$_GET['user_id'],
                'course_id' => 'eq.'.$_GET['course_id']
            ]
        ];
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if(empty($plans)){
            http_response_code(400);
            echo json_encode(array("msg" => "No plan record found"));
            exit;
        }

        $id = $plans[0]->id;
        $game_data = $plans[0]->game_data;

        // example game data:
        // {"game_data":{"holes":[{"shots":[{"gps":"28.295634098946294,-26.183867180911847","club":"7-iron","distance":"150 yards","shot_type":"fairway shot","shot_number":1},{"gps":"28.294888444839216,-26.183674623326148","club":"putter","distance":"30 yards","shot_type":"putt","shot_number":2}],"status":"complete","quick_sc":"details for quick SC","condition":{"green":"condition details","teebox":"condition details"},"hole_number":1,"hole_status":"birdie","total_putts":2,"total_shots":5}]}}


        $hole_data = null;
        $hole_status = null;
        foreach($game_data->holes as $hole_data_item){
            if($hole_data_item->hole_number == $hole){
                $hole_data = $hole_data_item;
                break;
            }
        }
        $plot_data = [];
        if(!empty($hole_data)){
            // get the status so we know to join all the lines if complete
            $hole_status = $hole_data->status;

            foreach($hole_data->shots as $shot){
                $plot_data[] = [
                    'type' => 'Feature',
                    'type' => 'Point',
                    'coordinates' => $shot->gps,
                    'club' => $shot->club,
                    'distance' => $shot->distance,
                    'shot_type' => $shot->shot_type,
                    'shot_number' => $shot->shot_number
                ];
            }
            $plot_data = json_encode($plot_data);
        }

        
    }else if($plan === 'match'){
        // create a supabase query to gimme_plan_game and retrieve the game_data json object
        $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
        $match_type = 'match';
        $status = 'pending';
        $query = [
           'select' => "*",
            'from' => "gimme_scores",
            'where' => [
                'user_id' => 'eq.'.$_GET['user_id'],
                'match_type' => 'eq.'.$match_type,
                'status' => 'eq.'.$status
            ]
        ];
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if(empty($plans)){
            http_response_code(400);
            echo json_encode(array("msg" => "No plan record found"));
            exit;
        }

        $id = $plans[0]->id;
        $game_data = $plans[0]->score_details;

        // example game data:
        // {"game_data":{"holes":[{"shots":[{"gps":"28.295634098946294,-26.183867180911847","club":"7-iron","distance":"150 yards","shot_type":"fairway shot","shot_number":1},{"gps":"28.294888444839216,-26.183674623326148","club":"putter","distance":"30 yards","shot_type":"putt","shot_number":2}],"status":"complete","quick_sc":"details for quick SC","condition":{"green":"condition details","teebox":"condition details"},"hole_number":1,"hole_status":"birdie","total_putts":2,"total_shots":5}]}}


        $hole_data = null;
        $hole_status = null;
        foreach($game_data->holes as $hole_data_item){
            if($hole_data_item->hole_number == $hole){
                $hole_data = $hole_data_item;
                break;
            }
        }
        $plot_data = [];
        if(!empty($hole_data)){
            // get the status so we know to join all the lines if complete
            $hole_status = $hole_data->status;

            foreach($hole_data->shots as $shot){
                $plot_data[] = [
                    'type' => 'Feature',
                    'type' => 'Point',
                    'coordinates' => $shot->gps,
                    'club' => $shot->club,
                    'distance' => $shot->distance,
                    'shot_type' => $shot->shot_type,
                    'shot_number' => $shot->shot_number
                ];
            }
            $plot_data = json_encode($plot_data);
        }
        
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golf Rangefinder with Mapbox</title>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.js'></script>
    <script src='https://npmcdn.com/@turf/turf/turf.min.js'></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        #map {
            width: 100%;
            height: 100vh;
        }
        .popup-content {
            max-width: 200px; /* Limit the width of the popup */
            font-family: Arial, sans-serif;
        }

        .popup-content h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
        }

        .popup-content p {
            margin: 0;
            font-size: 12px;
        }
        .custom-popup .mapboxgl-popup-content {
            background-color: #000; /* Dark background */
            color: #fff; /* White text color */
            font-family: Arial, sans-serif; /* Custom font */
        }

        /* Optionally style the popup tip */
        .custom-popup .mapboxgl-popup-tip {
            border-top-color: #fff; /* Match the popup content background */
        }
        .mapboxgl-popup-content {
            background: #000;
            color: #fff;
        }
        </style>
</head>
<body>
<div id="map"></div>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
mapboxgl.accessToken = "pk.eyJ1IjoiZ2ltbWVzdGF0IiwiYSI6ImNscmFqM2szYTBjd3EybW1ranA1ZDk4MzIifQ.hPv9rOyRX5gY2p4yAwdSig";

function updateLineSourceData(lineFeatures) {
    map.getSource('lineSource').setData({
        'type': 'FeatureCollection',
        'features': lineFeatures
    });
}

// Initialize the JS client
var supabase = supabase.createClient('https://yxafmkqlgognjztspaba.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inl4YWZta3FsZ29nbmp6dHNwYWJhIiwicm9sZSI6ImFub24iLCJpYXQiOjE2OTg5MDczMTEsImV4cCI6MjAxNDQ4MzMxMX0.wIHItuBaBwjhRBQrLl1jFppQ88pPv5NAy7mQYYqVHkw')

</script>

<?php if($load_map){ ?>
<script>

    var location_gps = <?= $location_gps ?>;
    var course_data = <?= $course_data ?>;
    console.log(course_data);
    var holeNumber = '<?= $hole ?>';
    var plot_data = <?= $plot_data ?>;
    var hole_status = '<?= $hole_status ?>';
    var popups = '<?= $popups ?>';
    var distance_pref = '<?= $distance_pref ?>';
    var user_id = '<?= $user_id ?>';
    var course_id = '<?= $course_id ?>';
    var mode = '<?= $mode ?>';
    var token = '<?= $token ?>';
    var id = '<?= $id ?>';
    console.log(plot_data);
    // get the count of the plot_data to determine what shot number this is
    var current_shot = plot_data.length + 1;

    // example of plot_data
    // [
    //     {
    //         "type": "Point",
    //         "coordinates": "28.295634098946294,-26.183867180911847",
    //         "club": "7-iron",
    //         "distance": "150 yards",
    //         "shot_type": "fairway shot",
    //         "shot_number": 1
    //     },
    //     {
    //         "type": "Point",
    //         "coordinates": "28.294888444839216,-26.183674623326148",
    //         "club": "putter",
    //         "distance": "30 yards",
    //         "shot_type": "putt",
    //         "shot_number": 2
    //     }
    // ]

    var el = document.createElement('div');
    el.className = 'marker'; // Optionally set a class for CSS styling
    el.style.backgroundImage = 'url(https://duendedisplay.co.za/gimme/api/v1/courses/assets/HolePos.svg)';
    el.style.width = '27px'; // Set the size of the icon
    el.style.height = '41px';
    el.style.backgroundSize = '100%';

    var el_tee = document.createElement('div');
    el_tee.className = 'marker'; // Optionally set a class for CSS styling
    el_tee.style.backgroundImage = 'url(https://duendedisplay.co.za/gimme/api/v1/courses/assets/TeeBoxPos.svg)';
    el_tee.style.width = '27px'; // Set the size of the icon
    el_tee.style.height = '41px';
    el_tee.style.backgroundSize = '100%';

    var holeKey = `hole_${holeNumber}`;
    var hole = course_data.course_data.hole_data[holeKey];
    // get the teebox gps
    var teebox = hole.tee_box.split(',').map(Number);
    var pin_location_gps = hole.pin_location_gps.split(',').map(Number);

    function convertDistanceNoText(distance_pref, distance){
        if(distance_pref == 'yards'){
            // convert from meters to yards
            distance = distance / 0.9144;
            distance = distance.toFixed(2);
            distance = distance;
            return distance;
        } else if(distance_pref == 'feet'){
            // convert from meters to feet
            distance = distance / 0.3048;
            distance = distance.toFixed(2);
            distance = distance;
            return distance;
        } else {
            distance = distance;
            return distance;
        }
    }

    function convertDistance(distance_pref, distance){
        if(distance_pref == 'yards'){
            // convert from meters to yards
            distance = distance / 0.9144;
            distance = distance.toFixed(2);
            distance = distance +' yards';
            return distance;
        } else if(distance_pref == 'feet'){
            // convert from meters to feet
            distance = distance / 0.3048;
            distance = distance.toFixed(2);
            distance = distance +' feet';
            return distance;
        } else {
            distance = distance +' meters';
            return distance;
        }
    }

    // Set map
    var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/satellite-v9', 
    center: teebox,
    zoom: 17
    });
    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

    var lineFeatures = [];
    var dynamicLines = [];

    var lineSourceData = {
        'type': 'geojson',
        'data': {
            'type': 'FeatureCollection',
            'features': [] // Initially empty; we'll add lines here dynamically
        }
    };

    map.on('load', function () {
        // Add source for lines
        map.addSource('lineSource', lineSourceData);

        // Add layer for lines from teeBox to clicked point
        map.addLayer({
            'id': 'lineToClickedPoint',
            'type': 'line',
            'source': 'lineSource',
            'layout': {
                'line-join': 'round',
                'line-cap': 'round'
            },
            'paint': {
                'line-color': 'white',
                'line-width': 4
            },
            'filter': ['==', '$type', 'LineString']
        });

        // Markers
        new mapboxgl.Marker(el_tee)
        .setLngLat(hole.tee_box.split(',').map(Number))
        .addTo(map);

        console.log('tee box');
        console.log(hole.tee_box.split(',').map(Number));
        console.log('pin location');
        console.log(hole.pin_location_gps.split(',').map(Number));

        new mapboxgl.Marker(el) 
        .setLngLat(hole.pin_location_gps.split(',').map(Number))
        .addTo(map);

        // if plot_data is not an empty array, add the plot data to the map and connect the lines to the markers
        if(plot_data.length > 0){

            var firstPlotPoint = plot_data[0].coordinates.split(',').map(Number);
            var firstLine = {
                "type": "Feature",
                "geometry": {
                    "type": "LineString",
                    "coordinates": [teebox, firstPlotPoint]
                }
            };
            lineFeatures.push(firstLine);

            for (var i = 0; i < plot_data.length; i++) {
                var el_plot_data = document.createElement('div');
                el_plot_data.className = 'marker';
                el_plot_data.style.backgroundImage = 'url(https://duendedisplay.co.za/gimme/api/v1/courses/assets/PlotPos.svg)';
                el_plot_data.style.width = '27px';
                el_plot_data.style.height = '41px';
                el_plot_data.style.backgroundSize = 'cover';

                // Assuming you might want to include specific information for each point
                var plot = plot_data[i];

                // if distance_pref is not 'meters' then convert the plot.distance to the distance_pref for example if distance_pref is 'yards' then convert the plot.distance to yards
                var distance = plot.distance;
                distance = convertDistance(distance_pref, distance);

                if(popups == 'true'){
                    var content = `
                        <div class="popup-content">
                            <h4>Shot/Putt ${i + 1}</h4>
                            <p><strong>Club:</strong> ${plot.club}</p>
                            <p><strong>Distance:</strong> ${distance}</p>
                            <p><strong>Shot Type:</strong> ${plot.shot_type}</p>
                        </div>
                    `;

                    var popup = new mapboxgl.Popup({ offset: 25, closeButton: false, closeOnClick: false })
                    .setLngLat(plot.coordinates.split(',').map(Number))
                    .setHTML(content)
                    .addTo(map);
                }

                // Create the marker without setting a popup to open on click
                var plotMarker = new mapboxgl.Marker(el_plot_data)
                    .setLngLat(plot.coordinates.split(',').map(Number))
                    .addTo(map);

                // Generate line feature for each plot marker
                if (i > 0) { // Ensures there's a previous plot to connect to
                    var previousPlot = plot_data[i - 1];
                    var line = {
                        "type": "Feature",
                        "geometry": {
                            "type": "LineString",
                            "coordinates": [
                                previousPlot.coordinates.split(',').map(Number),
                                plot.coordinates.split(',').map(Number)
                            ]
                        }
                    };
                    lineFeatures.push(line); // Add line feature to the array
                }
            }


            if (hole_status === 'complete' && plot_data.length > 0) {
                var lastPlotPoint = plot_data[plot_data.length - 1].coordinates.split(',').map(Number);
                var pinLocation = pin_location_gps;

                var finalLine = {
                    "type": "Feature",
                    "geometry": {
                        "type": "LineString",
                        "coordinates": [lastPlotPoint, pinLocation]
                    }
                };

                lineFeatures.push(finalLine); // Add this final line feature to the array
            }
            
            // Update line source with all line features after the loop
            updateLineSourceData(lineFeatures);
        }
    });

    var userMarker;
    let currentDistancePopup = null;
    let gimmePlan = null;

    // Handle click    
    map.on('click', function(e) {

        if(gimmePlan != null){
            // unsubscribe from the plan
            supabase.removeChannel(gimmePlan);
        }

        if(hole_status != 'complete'){
            if (userMarker) {
                userMarker.remove();
            }

            if (currentDistancePopup) {
                currentDistancePopup.remove();
            }

            var el_target = document.createElement('div');
            el_target.className = 'marker'; // Optionally set a class for CSS styling
            el_target.style.backgroundImage = 'url(https://duendedisplay.co.za/gimme/api/v1/courses/assets/TargetPos.svg)';
            el_target.style.width = '27px'; // Set the size of the icon
            el_target.style.height = '41px';
            el_target.style.backgroundSize = 'cover';

            userMarker = new mapboxgl.Marker(el_target)
                .setLngLat([e.lngLat.lng, e.lngLat.lat])
                .addTo(map);

            // Determine the starting point for the new line
            var startingPoint = teebox;
            var lastPlotCoordinates = [teebox[0], teebox[1]];
            if(plot_data.length > 0){
                lastPlotCoordinates = plot_data[plot_data.length - 1].coordinates.split(',').map(Number);
                startingPoint = lastPlotCoordinates;
            }

            var clickedPoint = turf.point([e.lngLat.lng, e.lngLat.lat]);
            var fromLastPoint = turf.point(lastPlotCoordinates);
            
            var options = {units: 'kilometers'};

            var distanceFromLastPoint = turf.distance(fromLastPoint, clickedPoint, options);
            var current_gps = e.lngLat.lng + ','+ e.lngLat.lat;

            // Generate the new dynamic lines
            var newDynamicLines = [
                {
                    "type": "Feature",
                    "geometry": {
                        "type": "LineString",
                        "coordinates": [startingPoint, [e.lngLat.lng, e.lngLat.lat]]
                    }
                },
                {
                    "type": "Feature",
                    "geometry": {
                        "type": "LineString",
                        "coordinates": [[e.lngLat.lng, e.lngLat.lat], pin_location_gps]
                    }
                }
            ];

            // Replace the dynamicLines array with the new lines
            dynamicLines = newDynamicLines;

            // Update the line source with both persistent and dynamic lines
            map.getSource('lineSource').setData({
                'type': 'FeatureCollection',
                'features': lineFeatures.concat(dynamicLines)
            });

            var distancePopupValue = (distanceFromLastPoint * 1000).toFixed(2);
            distancePopupValue = convertDistance(distance_pref, distancePopupValue);

            var distancePopupContent = `
                <div class="custom-popup-content">
                    <p>${distancePopupValue}</p>
                </div>
            `;

            var distancePopup = new mapboxgl.Popup({ offset: 25, closeButton: false, closeOnClick: false })
                .setLngLat([e.lngLat.lng, e.lngLat.lat])
                .setHTML(distancePopupContent)
                .addTo(map);

            // Update the reference to the current distancePopup
            currentDistancePopup = distancePopup;   

            var distancePopupValue = (distanceFromLastPoint * 1000).toFixed(2);
            distancePopupValue = convertDistanceNoText(distance_pref, distancePopupValue);
            
            // check if internet connection is available
                

            // send the current_gps and distancePopupValue to the api
            if (navigator.onLine) {
                $.ajax({
                    url: 'https://duendedisplay.co.za/gimme/api/v1/courses/update_gps.php',
                    method: "POST",
                    data: JSON.stringify({
                        token: token,
                        current_gps: current_gps,
                        distancePopupValue: distancePopupValue,
                        course_id: course_id,
                        user_id: user_id,
                        hole_id: holeNumber,
                        current_shot: current_shot,
                        distance_pref: distance_pref,
                        mode: mode
                    }),
                    success: function(response) {
                        console.log(response);

                        gimmePlan = supabase
                        .channel('gimmePlan')
                        .on('postgres_changes', { event: 'UPDATE', schema: 'public', table: 'gimme_plan_game', filter: 'id=eq.'+id }, payload => {
                            console.log('Change received!', payload);
                            var shots = payload.new.game_data.holes[holeNumber - 1].shots;
                            if (shots.length == current_shot) {
                                gimmePlan.unsubscribe();
                                console.log('shots length: ' + shots.length);
                                console.log('current_shot: ' + current_shot);
                                console.log('holeNumber: ' + holeNumber);
                                console.log('unsubscribed');
                                // reload the current URL
                                window.location.reload();
                            }


                        })
                        .subscribe();
                    }
                });
            } else {
                console.log('No internet connection');
            }
        }

    });

</script>
<?php } ?>
</body>
</html>    


