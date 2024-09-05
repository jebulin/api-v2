<?php

require_once 'enums/roles.enum.php';

require_once 'enums/approval.enum.php';

class Member
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function search($data)
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];
        $requiredFields = array('state', 'district', 'taluk', 'category');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo $data[$field];
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $state = mysqli_real_escape_string($this->conn, $data['state']);
            $district = mysqli_real_escape_string($this->conn, $data['district']);
            $taluk = mysqli_real_escape_string($this->conn, $data['taluk']);
            $category = mysqli_real_escape_string($this->conn, $data['category']);

            $query = "SELECT * from users where status = 1 and approval_status = 1 ";

            if (!empty($state)) $query .= "and state = '$state' ";
            if (!empty($district)) $query .= "and district = '$district' ";
            if (!empty($taluk)) $query .= "and taluk = '$taluk' ";
            if (!empty($category)) $query .= "and category = '$category' ";

            $query .= ";";

            $result = mysqli_query($this->conn, $query);
            $members = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $members[] = $row;
            }

            return array("http" => 200, "data" => $members, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all users password", "status" => false);
        }
    }
}
