<?php

require_once 'enums/roles.enum.php';

require_once 'enums/approval.enum.php';

require_once "email.php";

require_once 'service/generate_idcard.php';

class User
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getUser($data)
    {
        $userId =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            $requiredFields = array('id');

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    // Throw an error if any required field is missing
                    http_response_code(400);
                    return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
                }
            }
            $user_id = $data['id'];
            $query = "SELECT * from users where status = 1 and id = $user_id";
            $result = mysqli_query($this->conn, $query);
            // $user = mysqli_fetch_array($result);
            if ($user = $result->fetch_assoc()) {
                return array("http" => 200, "data" => $user, "status" => true);
            }
            return array("http" => 404, "msg" => "Not found", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all users password", "status" => false);
        }
    }

    public function getAllUsers()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * FROM users WHERE status = 1 ";
                $result1 = mysqli_query($this->conn, $query);
                
                $users = [];
                while ($userRow = mysqli_fetch_assoc($result1)) {
                    $users[] = $userRow;
                }

                return array("http" => 200, "data" => $users, "status" => true);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all users", "status" => false);
        }
    }

    public function getAllDeletedUsers()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * from users where status = 3";
                $result = mysqli_query($this->conn, $query);
                $users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }

                return array("http" => 200, "data" => $users, "status" => true);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get Deleted", "status" => false);
        }
    }

    public function getAllApprovedUsers()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * from users where status = 1 and approval_status = 1";
                $result = mysqli_query($this->conn, $query);
                $users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }

                return array("http" => 200, "data" => $users, "status" => true);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all approved users", "status" => false);
        }
    }

    public function getAllApprovalPendingUsers()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * from users where status = 1 and approval_status = 2";
                $result = mysqli_query($this->conn, $query);
                $users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }
            
                return array("http" => 200, "data" => $users, "status" => true);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all approval pending users", "status" => false);
        }
    }

    public function getAllDeclinedUsers()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * from users where status = 1 and approval_status = 3";
                $result = mysqli_query($this->conn, $query);
                $users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }

                return array("http" => 200, "data" => $users, "status" => true);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all decliend users", "status" => false);
        }
    }

    public function getAllApprovedAndDeclinedUsers()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * from users where status = 1 and approval_status != 2 order by id desc limit 20";
                $result = mysqli_query($this->conn, $query);
                $users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }

                return array("http" => 200, "data" => $users, "status" => true);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all Approved and decliend users", "status" => false);
        }
    }

    public function createUser($data)
    {
        $requiredFields = array('first_name', 'last_name', "phone_number", "role_id", "category", "taluk", "district", "state", 'email', 'aadhar', 'business_name', 'business_address');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                http_response_code(400);
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $email = mysqli_real_escape_string($this->conn, $data['email']);
                $first_name = mysqli_real_escape_string($this->conn, $data['first_name']);
                $last_name = mysqli_real_escape_string($this->conn, $data['last_name']);
                $phone_number = mysqli_real_escape_string($this->conn, $data['phone_number']);
                $approval_status = Approval::APPROVED;
                $data_role_id = mysqli_real_escape_string($this->conn, $data['role_id']);
                $aadhar = mysqli_real_escape_string($this->conn, $data['aadhar']);
                $businessName = mysqli_real_escape_string($this->conn, $data['business_name']);
                $businessAddress = mysqli_real_escape_string($this->conn, $data['business_address']);

                $checkQuery = "SELECT * from users where email='$email' or phone_number = '$phone_number'";
                $checkResult = mysqli_query($this->conn, $checkQuery);
                if ($checkResult->num_rows > 0) {
                    return array("http" => 500, "msg" => "User or phone number already present", "status" => false);
                }

                if ($role_id == Roles::ADMIN) {
                    if ($data_role_id == Roles::SUPER_ADMIN) {
                        return array("http" => 400, "msg" => "This role is not allowed to created by you", "status" => false);
                    }
                }
                $category = mysqli_real_escape_string($this->conn, $data['category']);
                $taluk = mysqli_real_escape_string($this->conn, $data['taluk']);
                $district = mysqli_real_escape_string($this->conn, $data['district']);
                $state = mysqli_real_escape_string($this->conn, $data['state']);

                $randomPassword = mt_rand(1000, 9999);
                $encryptedpassword = password_hash($randomPassword, PASSWORD_BCRYPT);

                $query = "INSERT INTO users (email, first_name, last_name,phone_number,aadhar,password,business_name,business_address, approval_status, role_id, category, taluk, district, state, created_by, approved_by)
                VALUES ('$email','$first_name','$last_name','$phone_number','$aadhar','$encryptedpassword','$businessName','$businessAddress',$approval_status,$data_role_id,'$category','$taluk','$district','$state',$user_id,$user_id) ";
                $result = mysqli_query($this->conn, $query);

                // $response = sendMail("$email,evsbookingoffice@gmail.com", "Your credentials", "Hi,\n username: $email \n Password: $randomPassword");
                $idCard = new IDCard($this->conn);
                $query = "SELECT * from users where status = 1 and email = '$email'";
                $userFetchQuery = mysqli_query($this->conn, $query);
                if ($userFetch = $userFetchQuery->fetch_assoc()) {
                    $id = $idCard->generateIdCard($userFetch, $randomPassword);
                }
                if ($id['http'] == 200) {
                    return array("http" => 200, "msg" => "User created with email Id : $email", "status" => true);
                }
                return array("http" => 200, "msg" => "User created with email Id : $email, but id card is not sent", "status" => false);
            }

            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            // echo $e;
            return array("http" => 500, "msg" => "Error in user create", "status" => false);
        }
    }

    public function updateuser($data)
    {
        $requiredFields = array('first_name', 'last_name', "phone_number", "role_id", "category", "taluk", "district", "state", 'email');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                http_response_code(400);
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }
        $email = $data["email"];

        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            $query = "SELECT * from users where email = '$email'";

            // Execute the query
            $result = mysqli_query($this->conn, $query);
            if ($result->num_rows > 0) {
                $email = mysqli_real_escape_string($this->conn, $data['email']);
                $first_name = mysqli_real_escape_string($this->conn, $data['first_name']);
                $last_name = mysqli_real_escape_string($this->conn, $data['last_name']);
                $phone_number = mysqli_real_escape_string($this->conn, $data['phone_number']);
                $approval_status = Approval::APPROVED;
                $data_role_id = mysqli_real_escape_string($this->conn, $data['role_id']);
                $category = mysqli_real_escape_string($this->conn, $data['category']);
                $taluk = mysqli_real_escape_string($this->conn, $data['taluk']);
                $district = mysqli_real_escape_string($this->conn, $data['district']);
                $state = mysqli_real_escape_string($this->conn, $data['state']);

                $query = "INSERT INTO users (email, first_name, last_name,phone_number, approval_status, approved_by,role_id, category, taluk, district, state, created_by, approved_by)
                VALUES ('$email','$first_name','$last_name','$phone_number',$approval_status,$user_id,$data_role_id,'$category','$taluk','$district','$state',$user_id,$user_id) ";
                $result = mysqli_query($this->conn, $query);

                return array("http" => 200, "msg" => "Details updated", "status" => true);
            }

            return array("http" => 404, "msg" => "User not present", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all users password", "status" => false);
        }
    }

    public function changePassword($data)
    {
        $requiredFields = array('newPassword', 'oldPassword');
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }
        $newPassword = $data["newPassword"];
        $oldPassword = $data['oldPassword'];
        try {
            $query = "SELECT * from users where id = $user_id";

            // Execute the query
            $result = mysqli_query($this->conn, $query);

            // Check if there are any results
            if ($result->num_rows > 0) {
                // Fetch the results and process them
                while ($row = $result->fetch_assoc()) {
                    if (password_verify($oldPassword, $row['password'])) {
                        $encryptedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                        $updateQuery = "UPDATE users SET password = '$encryptedPassword' WHERE id = $user_id";

                        // Execute the query
                        $result = mysqli_query($this->conn, $updateQuery);
                        break;
                    } else {
                        return array("http" => 500, "msg" => "Entered old password is wrong", "status" => false);
                    }
                    break;
                    // Process each row
                    // Example: echo $row["column_name"];
                }
            } else {
                // Handle case when there are no results
                return array("http" => 500, "msg" => "User not found", "status" => false);
            }

            return array("http" => 200, "msg" => "password changed successfully", "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all users password", "status" => false);
        }
    }

    function approvalStatus($data)
    {
        $requiredFields = array('email', "status");

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }
        $email = $data["email"];
        $status = $data["status"];

        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        $randomPassword = mt_rand(1000, 9999);
        $encryptedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);
        try {
            if ($role_id == Roles::SUPER_ADMIN || $role_id == Roles::ADMIN) {
                $query = "SELECT * from users where email = '$email'";

                // Execute the query
                $result = mysqli_query($this->conn, $query);
                $updateQuery = "";
                while ($row = $result->fetch_assoc()) {
                    if ($status == 1) {

                        $updateQuery = "UPDATE users SET approval_status = $status, password='$encryptedPassword', updated_by=$user_id WHERE email = '$email'";
                    } else {
                        $updateQuery = "DELETE FROM users WHERE email = '$email'";
                    }

                    // Execute the query
                    $result = mysqli_query($this->conn, $updateQuery);

                    if ($status == 1) {

                        // $response = sendMail("$email,evsbookingoffice@gmail.com", "Your credentials", "Hi,\n username: $email \n Password: $randomPassword");
                        $idCard = new IDCard($this->conn);
                        $query = "SELECT * from users where status = 1 and email = '$email'";
                        $userFetchQuery = mysqli_query($this->conn, $query);
                        if ($userFetch = $userFetchQuery->fetch_assoc()) {
                            $id = $idCard->generateIdCard($userFetch,$randomPassword);
                        }
                    }

                    return array("http" => 200, "msg" => "User status is changed", "status" => true);
                }
                return array("http" => 500, "msg" => "User not present", "status" => false);
            }
            return array("http" => 403, "msg" => "User is not allowed", "status" => false);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Error in approval", "status" => false);
        }
    }

    function verifyEmail()
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        try {
            $query = "SELECT * from users where id = $user_id";

            // Execute the query
            $result = mysqli_query($this->conn, $query);

            while ($row = $result->fetch_assoc()) {
                if ($row['is_verified'] != 1) {
                    $otp = mt_rand(1000, 9999);
                    $updateQuery = "UPDATE users SET otp = $otp WHERE id = $user_id";
                    $udpate = mysqli_query($this->conn, $updateQuery);

                    $response = sendMail($row['email'], "Email Verification", "Hi,\n The OTP is $otp");

                    if ($response) {
                        return array("http" => 200, "msg" => "OTP verification is sent to the :" . $row['email'], "status" => true);
                    } else {
                        return array("http" => 500, "msg" => "Error while sending email", "status" => false);
                    }
                } else {
                    return array("http" => 400, "msg" => "User is already verified", "status" => false);
                }
            }
        } catch (Exception $e) {
            return array("http" => 500, "msg" => "Error while verifying", "status" => false);
        }
    }
}

    // public function checkEmail()
    // {
    //     try {
    //         $response = sendMail("Jebulin3@gmail.com", "This is the password", "OTP is 0129");
    //         echo $response;
    //         if ($response == "success")
    //             return array("http" => 200, "msg" => $response, "status" => true);
    //     } catch (Exception $e) {
    //         echo $e;
    //     }
    //     return array("http" => 500, "msg" => $response, "status" => false);
    // }
