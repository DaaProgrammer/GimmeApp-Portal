
<?php 
    // display errors
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
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
    $course_id = $_POST['course_id'];
    $filterby = $_POST['filterby'];

    $query = $supabase->initializeQueryBuilder();

    try{
        $tee_data = $query->select('tee_data')
            ->from('gimme_courses')
            ->where('id', 'eq.'.$course_id)
            ->order('id.desc')
            ->execute()
            ->getResult();
        
        $filtered_tee_data = [];
        foreach ($tee_data[0]->tee_data->tees as $tee_color => $tee_details) {
            // Access tee details like $tee_details->par, $tee_details->slope, $tee_details->gender, $tee_details->rating
       
            if($tee_details->gender==$filterby){
                $filtered_tee_data[] = $tee_details->par;
            }
            // Example usage: $tee_color, $tee_details->par, $tee_details->slope, $tee_details->gender, $tee_details->rating
        }

        http_response_code(200);
        echo json_encode(array("msg" => "Success",'data' => $filtered_tee_data));
    }
    catch(Exception $e){
        http_response_code(500);
        echo json_encode(array("msg" => "Something went wrong"));
    }

?>
