<?php
session_start();
require_once 'config/config.php';
require_once 'service/users.php';
require_once 'service/news-letters.php';
require_once 'service/reports.php';
require_once 'service/members.php';
require_once 'middleware/authorization.middleware.php';
// require_once 'service/google.php';
require_once 'service/generate_idcard.php';
require_once "service/otp.php";

$endpoint = $_SERVER['PATH_INFO'];
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '100M');
ini_set('max_input_vars', '3000');
// Use the authorize function to check if the request is authorized
authorize();

$userObj = new User($conn);
$newsLetterObj = new NewsLetter($conn);
$reportObj = new Report($conn);
$memObj = new Member($conn);
// $google = new GoogleDriveUpload($conn);
$idCard = new IDCard($conn);
$otpObj = new OTP($conn);

$method = $_SERVER['REQUEST_METHOD'];

function cleanArray($array) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = cleanArray($value);
        } elseif (is_string($value)) {
            // Remove or replace special characters
            $array[$key] = preg_replace('/[^\x20-\x7E]/','', $value); // Removes non-printable ASCII characters
            // Alternatively, you can remove or replace specific unwanted characters
            //$array[$key] = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        }
    }
    return $array;
}

header('Content-Type: application/json');

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Dynamically set the Access-Control-Allow-Origin header
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    // header('Access-Control-Max-Age: 86400');    // Cache for 1 day
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}

// Specify which request methods are allowed
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

switch ($method) {
    case 'GET':
        switch ($endpoint) {
            case '/user/get/all':
                $users = $userObj->getAllUsers();
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/get/rejected-users':
                $users = $userObj->getAllDeletedUsers();
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/get/approved-users':
                $users = $userObj->getAllApprovedUsers();
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/get/approval-pending-users':
                $users = $userObj->getAllApprovalPendingUsers();
                http_response_code(current($users));
                $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/get/declined-users':
                $users = $userObj->getAllDeclinedUsers();
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/get/approved-and-declined-users':
                $users = $userObj->getAllApprovedAndDeclinedUsers();
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/verify-email':
                $users = $userObj->verifyEmail();
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        switch ($endpoint) {
            case '/user/get':
                $users = $userObj->getUser($data);
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/create':
                $users = $userObj->createUser($data);
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/change-password':
                $users = $userObj->changePassword($data);
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;
            case '/user/approval-status': //approve/ disapprove
                $users = $userObj->approvalStatus($data);
                http_response_code(current($users));
                 $users = cleanArray($users);
                echo json_encode($users);
                break;


            case '/members/search':
                $members = $memObj->search($data);
                http_response_code(current($members));
                 $members = cleanArray($members);
                echo json_encode($members);
                break;


                ////newsletters
            case '/news-letters/create':
                $newLetter = $newsLetterObj->createNewsLetter($data);
                http_response_code(current($newLetter));
                 $newLetter = cleanArray($newLetter);
                echo json_encode($newLetter);
                break;
            case '/news-letters/get/all':
                $newsLetters = $newsLetterObj->getAllNewsLetters($data);
                http_response_code(current($newsLetters));
                 $newsLetters = cleanArray($newsLetters);
                echo json_encode($newsLetters);
                break;
            case '/news-letters/get/one':
                $newLetter = $newsLetterObj->getOneNewsLetter($data);
                http_response_code(current($newLetter));
                 $newLetter = cleanArray($newLetter);
                echo json_encode($newLetter);
                break;
            case '/news-letters/update':
                $newLetter = $newsLetterObj->updateNewsLetter($data);
                http_response_code(current($newLetter));
                 $newLetter = cleanArray($newLetter);
                echo json_encode($newLetter);
                break;


                ///reports
            case '/reports/create':
                $reports = $reportObj->createReport($data);
                http_response_code(current($reports));
                 $reports = cleanArray($reports);
                echo json_encode($reports);
                break;
            case '/reports/get/all':
                $reports = $reportObj->getAllReports($data);
                http_response_code(current($reports));
                 $reports = cleanArray($reports);
                echo json_encode($reports);
                break;
            case '/reports/get/one':
                $report = $reportObj->getOneReport($data);
                http_response_code(current($report));
                 $report = cleanArray($report);
                echo json_encode($report);
                break;
            case '/reports/update':
                $report = $reportObj->updateReport($data);
                http_response_code(current($report));
                 $report = cleanArray($report);
                echo json_encode($report);
                break;


            case '/generate/id-card':
                $id = $idCard->generateIdCard($data, "null");
                http_response_code(current($id));
                 $id = cleanArray($id);
                echo json_encode($id);
                break;

                

                //otp
            case '/user/send-otp-to-mobile':
                $users = $otpObj->sendOTPToPhone($data);
                http_response_code(current($users));
                echo json_encode($users);
                break;
        }
        break;

    default:
        $default = array("http" => 404, "msg" => "path not found", "status" => false);
        http_response_code(current($default));
         $default = cleanArray($default);
        echo json_encode($default);
        break;
}
