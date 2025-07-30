<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once '../../Tools/Supabase/vendor/autoload.php';
    $config = require_once '../../Tools/Supabase/config.php';

    $supabase = new PHPSupabase\Service(
        $config['supabaseKey'],
        $config['supabaseUrl']
    );
    
    class Notifications{

        private $app_id;
        private $rest_api_key;

        public function __construct() {
            $this->app_id = '730724a4-5226-4f3d-8208-ef407df537c9';
            $this->rest_api_key = 'MWMyMTliNmMtOWE3YS00NmJmLWEwNDItMDkxYzk2NGU0NWJl';
        }

        function sendNotification($player_id,$headings,$contents){
            $ch = curl_init();
            // Set the URL for the OneSignal API endpoint
            $url = "https://onesignal.com/api/v1/notifications";

            // Set the headers for the API request
            $headers = array(
                'Authorization: Basic ' . $this->rest_api_key,
                'Content-Type: application/json');

            // Set the notification data
            $data = array(
                'app_id' => $this->app_id,
                'include_player_ids' => array($player_id),
                'headings' => array('en' => $headings),
                'contents' => array('en' => $contents),
                'data' => array('message_sent' => 'value'),
                'large_icon' => 'https://duendedisplay.co.za/equinetendon/api/Assets/appstore.png',
                'small_icon' => 'https://duendedisplay.co.za/equinetendon/api/Assets/appstore.png');

            // Convert the data to JSON format
            $json_data = json_encode($data);
            // Set the options for the curl request
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            // Execute the curl request
            $result = curl_exec($ch);
            // Check for errors
            if (curl_errno($ch)) {
                // Close the curl connection
                curl_close($ch);
                return false;
            } else {
                // Close the curl connection
                curl_close($ch);
                return true;
            }
        }

        function notifyAllEventUsers($eventID, $headings,$contents){
            // Declarations
            $all_user_player_ids = [];
            $ch = curl_init();

            // Set the URL for the OneSignal API endpoint
            $url = "https://onesignal.com/api/v1/notifications";
            
            // Set the headers for the API request
            $headers = array(
                'Authorization: Basic ' . $this->rest_api_key,
                'Content-Type: application/json');

            // Users
            // Get all user player ids and populate to array
            // global $supabase;
            $users_db = $this->supabase->initializeDatabase('gimme_users','id');
            $users = $users_db->findAll()->getResult();
            foreach($users as $user) {
                array_push($all_user_player_ids, $user->player_id);
            }

            // Send notification
            $data = array(
                'app_id' => $this->app_id,
                'include_player_ids' => $all_user_player_ids,
                'headings' => array('en' => $headings),
                'contents' => array('en' => $contents),
                'data' => array('message_sent' => 'value'),
                'large_icon' => 'https://storage.googleapis.com/flutterflow-io-6f20.appspot.com/projects/my-brada-goztlj/assets/ks7imxmskved/logo.png',
                'small_icon' => 'ic_launcher');
            // Convert the data to JSON format
            $json_data = json_encode($data);
            // Set the options for the curl request
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            // Execute the curl request
            $result = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                // Close the curl connection
                curl_close($ch);
                // echo "Error";
                return false;
            } else {
                // Close the curl connection
                curl_close($ch);
                // echo "Success";
                return true;
            }
        }

        /*
            On each new install of the app, OneSignal creates a new subscription.
        */
        function removeOldSubscription($oldPlayerID){
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://onesignal.com/api/v1/apps/$this->app_id/subscriptions/$oldPlayerID",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => 'DELETE',
              CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $this->rest_api_key",
                'Content-Type: application/json'
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            
            // Uncomment to test
            // echo $response;
        }
    }
?>