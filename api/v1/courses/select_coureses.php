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
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$_POST = json_decode(file_get_contents('php://input'), true);

require '../auth/token.php';
require_once '../util/util.php';

// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$courses_db = $supabase->initializeDatabase('gimme_courses','id');

$searchQuery = isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '';
$latitude = isset($_POST['lat']) ? $_POST['lat'] : null;
$longitude = isset($_POST['long']) ? $_POST['long'] : null;

try{
    if(!empty($searchQuery)){
        $courses = $courses_db->fetchAll()->getResult();
        $searchQuery = strtolower($searchQuery); // Convert search query to lowercase for case-insensitive search
        usort($courses, function($a, $b) use ($searchQuery) {
            $aPos = stripos(strtolower($a->course_name), $searchQuery);
            $bPos = stripos(strtolower($b->course_name), $searchQuery);
            if ($aPos === false) {
                $aPos = PHP_INT_MAX;
            }
            if ($bPos === false) {
                $bPos = PHP_INT_MAX;
            }
            return $aPos <=> $bPos;
        });
        $courses = array_filter($courses, function($course) use ($searchQuery) {
            return stripos(strtolower($course->course_name), $searchQuery) !== false;
        });

    } else {
        // Get all courses
        $courses = $courses_db->fetchAll()->getResult();
    }

    // If GPS coordinates are provided, filter courses by nearest location
    if($latitude !== null && $longitude !== null){
        usort($courses, function($a, $b) use ($latitude, $longitude) {
            $coordinatesA = explode(',', $a->location_gps); // "location_gps": "31.0493058,-29.7819141"
            $coordinatesB = explode(',', $b->location_gps);

            $earthRadius = 6371; // Earth's radius in kilometers

            $latFrom = deg2rad((float)$latitude);
            $lonFrom = deg2rad((float)$longitude);

            $latToA = deg2rad((float)$coordinatesA[0]);
            $lonToA = deg2rad((float)$coordinatesA[1]);
            $latToB = deg2rad((float)$coordinatesB[0]);
            $lonToB = deg2rad((float)$coordinatesB[1]);

            $latDeltaA = $latToA - $latFrom;
            $lonDeltaA = $lonToA - $lonFrom;
            $latDeltaB = $latToB - $latFrom;
            $lonDeltaB = $lonToB - $lonFrom;

            $angleA = 2 * asin(sqrt(pow(sin($latDeltaA / 2), 2) + cos($latFrom) * cos($latToA) * pow(sin($lonDeltaA / 2), 2)));
            $angleB = 2 * asin(sqrt(pow(sin($latDeltaB / 2), 2) + cos($latFrom) * cos($latToB) * pow(sin($lonDeltaB / 2), 2)));

            $distanceA = $angleA * $earthRadius;
            $distanceB = $angleB * $earthRadius;

            return $distanceA <=> $distanceB;
        });
    }

    http_response_code(200);
    echo json_encode(array("courses" => $courses));
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}

?>