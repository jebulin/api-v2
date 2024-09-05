<?php
function authorize() {
    if (!isset($_SESSION['id'])) {
        // If token is not set or is invalid, return unauthorized status
        echo isset($_SESSION['id']);
        echo "Session expired";
        http_response_code(401);
        exit();
    }
}
?>