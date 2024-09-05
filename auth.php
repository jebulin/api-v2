<?php

require_once 'config/config.php';
require_once 'service/login.php';

$loginObj = new Login($conn);

$method = $_SERVER['REQUEST_METHOD'];

$endpoint = $_SERVER['PATH_INFO'];

header('Content-Type: application/json');

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Dynamically set the Access-Control-Allow-Origin header
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // Cache for 1 day
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

    case 'POST':
        switch ($endpoint) {
            case '/login':
                $data = json_decode(file_get_contents('php://input'), true);
                $users = $loginObj->verifyUser($data);
                http_response_code(current($users));
                echo json_encode($users);
                break;
            case '/forgot-password':
                $data = json_decode(file_get_contents('php://input'), true);
                $users = $loginObj->forgotPassword($data);
                http_response_code(current($users));
                echo json_encode($users);
                break;
            case '/user/register':
                $data = json_decode(file_get_contents('php://input'), true);
                $users = $loginObj->registerUser($data);
                http_response_code(current($users));
                echo json_encode($users);
                break;
            case '/verify/email':
                $data = json_decode(file_get_contents('php://input'), true);
                $users = $loginObj->verifyEmail($data);
                http_response_code(current($users));
                echo json_encode($users);
                break;
            case '/check-email':
                $data = json_decode(file_get_contents('php://input'), true);
                $users = $loginObj->checkEmail($data);
                http_response_code(current($users));
                echo json_encode($users);
                break;
        }
        break;


    case 'GET':
        switch ($endpoint) {
            case "/logout":
                $result = $loginObj->logout();
                http_response_code(current($result));
                echo json_encode($result);
                break;
        }
}
