<?php
session_start();


require_once 'enums/roles.enum.php';

require_once 'enums/approval.enum.php';

require_once "email.php";

require_once "otp.php";

class Login
{
    private $conn;
    public $otpObj;


    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->otpObj = new OTP($conn);

    }
    public function verifyUser($data)
    {
        $username = $data['username'];
        $password = $data['password'];
        $requiredFields = array('username', 'password');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                http_response_code(400);
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        $query = "SELECT * from users where email=\"$username\"";
        try {
            $result = mysqli_query($this->conn, $query);

            $row = mysqli_fetch_array($result);
            if (is_array($row)) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION["id"] = $row["id"];
                    $_SESSION['role_id'] = $row["role_id"];
                    return array("http" => 200, "msg" => "Logged in successfully", "access_token" => session_id(), "role_id" => $row["role_id"], "first_name" => $row["first_name"], "last_name" => $row["last_name"], "user_id" => $row["id"], "status" => true);
                } else {
                    return array("http" => 401, "msg" => "UnAuthorized", "status" => false);
                }
            } else {
                return array("http" => 404, "msg" => "User not Present", "status" => false);
            }
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in verify user", "status" => false);
        }
    }

    public function forgotPassword($data)
    {
        $email = $data['email'];
        $query = "SELECT * from users where email=\"$email\"";
        try {
            $result = mysqli_query($this->conn, $query);

            $row = mysqli_fetch_array($result);
            if (is_array($row)) {
                $id = $row['id'];
                $randomPassword = rand(100000, 999999);
                $encryptedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);
                $updateQuery = "UPDATE users SET password = '$encryptedPassword' WHERE email = '$email'";

                // Execute the query
                $result = mysqli_query($this->conn, $updateQuery);

                $response = sendMail($row['email'], "Reset Password mail", "Hi,\n the New password is $randomPassword");

                if ($response == 'success') {
                    return array("http" => 200, "msg" => "Mail sent", "status" => true);
                }
                return array("http" => 500, "msg" => "Mail send error", "status" => false);
            } else {

                return array("http" => 404, "msg" => "User not Present", "status" => false);
            }
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in forgot password", "status" => false);
        }
    }

    public function logout()
    {
        unset($_SESSION['id']);
        unset($_SESSION['role_id']);
        session_destroy();
        return array("http" => 200, "msg" => "Logout successfull", "status" => true);
    }

    public function registerUser($data)
    {
        $requiredFields = array('first_name', 'last_name', "phone_number", "category", "taluk", "district", "state", 'email', "aadhar", 'business_name', 'business_address');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $email = mysqli_real_escape_string($this->conn, $data['email']);
            $first_name = mysqli_real_escape_string($this->conn, $data['first_name']);
            $last_name = mysqli_real_escape_string($this->conn, $data['last_name']);
            $phone_number = mysqli_real_escape_string($this->conn, $data['phone_number']);
            $approval_status = Approval::PENDING;
            $role_id = Roles::STANDARD_USER;
            $category = mysqli_real_escape_string($this->conn, $data['category']);
            $taluk = mysqli_real_escape_string($this->conn, $data['taluk']);
            $district = mysqli_real_escape_string($this->conn, $data['district']);
            $state = mysqli_real_escape_string($this->conn, $data['state']);
            $aadhar = mysqli_real_escape_string($this->conn, $data['aadhar']);
            $businessName = mysqli_real_escape_string($this->conn, $data['business_name']);
            $businessAddress = mysqli_real_escape_string($this->conn, $data['business_address']);

            $otp = rand(10000, 99999);

            // $checkQuery = "SELECT * from users where email='$email' or phone_number = '$phone_number'";
            // $checkResult = mysqli_query($this->conn, $checkQuery);
            // if ($checkResult->num_rows > 0) {
            //     return array("http" => 500, "msg" => "User or phone number already present", "status" => false);
            // }

            $query = "INSERT INTO users (email, first_name, last_name,phone_number, aadhar,business_name,business_address, otp, approval_status, role_id, category, taluk, district, state)
                VALUES ('$email','$first_name','$last_name','$phone_number','$aadhar','$businessName','$businessAddress','$otp',$approval_status,$role_id,'$category','$taluk','$district','$state') ";
            $result = mysqli_query($this->conn, $query);

            $this->otpObj->sendRequest2Factor($phone_number, $otp);

            // $response = sendMail($email, "Email Verification", "Hi,\n The OTP is $otp");

            if ($result) {
                return array("http" => 200, "msg" => "User is registered and sent for approval", "status" => true);
            } else {
                return array("http" => 500, "msg" => "Error while creating user", "status" => false);
            }
        } catch (Exception $e) {
            error_log($e);
        }
        return array("http" => 500, "msg" => "Internal server while registering user", "status" => false);
    }

    public function verifyEmail($data)
    {
        $requiredFields = array('otp', 'email');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        $email = mysqli_real_escape_string($this->conn, $data['email']);
        $otp = mysqli_real_escape_string($this->conn, $data['otp']);

        $query = "SELECT * from users where email = '$email' and is_verified=0";
        $result = mysqli_query($this->conn, $query);

        if ($result->num_rows > 0) {
            // Fetch the results and process them
            while ($row = $result->fetch_assoc()) {
                if ($row['otp'] == $otp) {
                    $updateQuery = "UPDATE users SET is_verified = 1 WHERE email = '$email'";

                    // Execute the query
                    $result = mysqli_query($this->conn, $updateQuery);
                    return array("http" => 200, "data" => "User is verified", "status" => true);
                } else {
                    return array("http" => 400, "data" => "Wrong OTP", "status" => false);
                }
                break;
                // Process each row
                // Example: echo $row["column_name"];
            }
        }
        // Handle case when there are no results
        return array("http" => 500, "msg" => "Email not present", "status" => false);
    }


    public function checkEmail($data)
    {
        $requiredFields = array('email');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }
        $email = mysqli_real_escape_string($this->conn, $data['email']);
        $query = "SELECT * from users where email = '$email' ";
        $result = mysqli_query($this->conn, $query);

        if ($result->num_rows > 0) {
            // Fetch the results and process them
            return array("http" => 400, "msg" => "email already present", "status" => false);
        }

        return array("http" => 200, "msg" => "email not present", "status" => true);
    }

    
    function verifyPhoneNumber($data)
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        $requiredFields = array('email', "otp");

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }
        $email = $data["email"];
        $otp = $data["otp"];

        try {
            $query = "SELECT * from users where email = $email";

            // Execute the query
            $result = mysqli_query($this->conn, $query);

            while ($row = $result->fetch_assoc()) {
                if ($row['is_verified'] == 0) {
                    if ($row['otp'] == $otp) {
                        $updateQuery = "UPDATE users SET is_verified = 1, otp = null WHERE email = $email";
                        $update = mysqli_query($this->conn, $updateQuery);
                        return array("http" => 200, "msg" => "Phone number is successfully verified", "status" => true);
                    } else {
                        return array("http" => 500, "msg" => "OTP is wrong", "status" => false);
                    }
                } else {
                    return array("http" => 400, "msg" => "User is already verified", "status" => false);
                }
            }
            return array("http" => 404, "msg" => "User not found", "status" => false);
        } catch (Exception $e) {
            return array("http" => 500, "msg" => "Error while verifying", "status" => false);
        }
    }

}
