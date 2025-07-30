<?php 
// error_reporting(E_ALL & ~E_DEPRECATED);
// ini_set('display_errors', '1');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    http_response_code(200); // Respond with OK status
    exit(0);
}

$fileUrl = $_POST['url_link'];
$folder = $_POST['folder'];
$timestamp = time();

// Validate folders

$tempFile = tempnam(sys_get_temp_dir(), 'temp');
file_put_contents($tempFile, fopen($fileUrl, 'r'));
$folderName = '/gimme/uploads/'.$folder.'/'; 
$remoteFileName = basename($fileUrl);

// // MIME type detection for setting ContentType correctly
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tempFile);

$SPACE = "duende";
$REGION = "fra1";
$STORAGETYPE = "STANDARD";
$KEY = "DO00ZENA2RDXMQ2V737Z";
$SECRET = "scMQXy+OIoJTZV+o/+2DwJ9lDm3VscAYFbhwTVvKC0w";

function putS3($filePath, $remotePath, $mimeType, $fileUrl) {
    global $SPACE, $REGION, $STORAGETYPE, $KEY, $SECRET;

    $timestamp = time();
    $fileName = $timestamp.'-'.basename($fileUrl);
    $date = gmdate('D, d M Y H:i:s T');
    $acl = "x-amz-acl:public-read";
    $content_type = $mimeType;
    $storage_type = "x-amz-storage-class:$STORAGETYPE";

    $string = "PUT\n\n$content_type\n$date\n$acl\n$storage_type\n/$SPACE$remotePath$fileName";
    $signature = base64_encode(hash_hmac('sha1', $string, $SECRET, true));

    $ch = curl_init("https://$SPACE.$REGION.digitaloceanspaces.com$remotePath$fileName");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Host: $SPACE.$REGION.digitaloceanspaces.com",
        "Date: $date",
        "Content-Type: $content_type",
        $storage_type,
        $acl,
        "Authorization: AWS $KEY:$signature"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }
    curl_close($ch);
    //echo "Upload successful: $response\n";
    http_response_code(200);
    echo json_encode(array("msg" => "Upload Complete", "url" => "https://$SPACE.$REGION.cdn.digitaloceanspaces.com".$remotePath.$fileName));
    exit;
}

$filePath = $tempFile; // Update to your file path
$remotePath = $folderName; // Update to your desired remote path
putS3($filePath, $remotePath, $mimeType, $fileUrl);

?>