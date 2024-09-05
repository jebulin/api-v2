<?php

require_once 'enums/roles.enum.php';

require_once 'enums/approval.enum.php';

class OTP
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    function sendRequest2Factor($number, $otp)
    {
        try {
            $url = "https://2factor.in/API/V1/d6fd32dd-574f-11ef-8b60-0200cd936042/SMS/+91$number/$otp/Evahan Sevai - OTP";

            $response = file_get_contents($url);
            $responseArray = json_decode($response, true);

            if (is_array($responseArray) && isset($responseArray['Status'])) {
                $status = $responseArray['Status'];
                if ($status == "Success") {
                    return "success";
                }
            }
        } catch (Exception $e) {
            echo $e;
        }
        return "failure";
    }

    function sendOTPToPhone($data)
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        $requiredFields = array('email');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }
        $email = $data["email"];

        try {
            $query = "SELECT * from users where email = '$email'";

            // Execute the query
            $result = mysqli_query($this->conn, $query);

            while ($row = $result->fetch_assoc()) {
                if ($row['is_verified'] == 0) {
                    $otp = mt_rand(1000, 9999);
                    $updateQuery = "UPDATE users SET otp = $otp WHERE email = '$email'";
                    $udpate = mysqli_query($this->conn, $updateQuery);
                    $sendRequest = $this->sendRequest2Factor($row['phone_number'], $otp);
                    if ($sendRequest == "success") {
                        return array("http" => 200, "msg" => "OTP is sent successfully", "status" => true);
                    } else {
                        return array("http" => 500, "msg" => "OTP is not sent", "status" => false);
                    }
                } else {
                    return array("http" => 400, "msg" => "User is already verified", "status" => false);
                }
            }
            return array("http" => 404, "msg" => "User not found", "status" => false);
        } catch (Exception $e) {
            echo $e;
            return array("http" => 500, "msg" => "Error while verifying", "status" => false);
        }
    }
}
