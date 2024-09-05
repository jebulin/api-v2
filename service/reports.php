<?php

require_once 'enums/roles.enum.php';

require_once 'enums/approval.enum.php';

class Report
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createReport($data)
    {
        $created_by =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        $requiredFields = array('report', "user_id");

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $report = mysqli_real_escape_string($this->conn, $data['report']);

            $user_id = mysqli_real_escape_string($this->conn, $data['user_id']);

            $query = "INSERT INTO reports (user_id, report, created_by, status)
                VALUES ('$user_id','$report', $created_by, 2); ";

            $result = mysqli_query($this->conn, $query);

            return array("http" => 200, "msg" => "Reported", "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in create report", "status" => false);
        }
    }

    public function getAllReports($data)
    {

        $requiredFields = array('limit', "offset");

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $limit = mysqli_real_escape_string($this->conn, $data['limit']);
            $offset = mysqli_real_escape_string($this->conn, $data['offset']) ;

            $query = "SELECT * from reports order by id desc limit $limit offset $offset";
            $result = mysqli_query($this->conn, $query);
            $reports = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $reports[] = $row;
            }

            return array("http" => 200, "data" => $reports, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all reports", "status" => false);
        }
    }

    public function getOneReport($data)
    {
        $requiredFields = array('id');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $id = $data['id'];
            $query = "SELECT * from reports where id = $id";
            $result = mysqli_query($this->conn, $query);
            $reports = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $reports[] = $row;
            }
            return array("http" => 200, "data" => $reports, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get one report", "status" => false);
        }
    }

    public function updateReport($data)
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        if ($role_id != Roles::SUPER_ADMIN && $role_id != Roles::ADMIN) {
            return array("http" => 403, "msg" => "Unauthorized", "status" => false);
        }
        
        $requiredFields = array('id', 'status');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $status = mysqli_real_escape_string($this->conn, $data['status']);
            $id = mysqli_real_escape_string($this->conn, $data['id']);

            $query = "UPDATE reports SET status = $status, updated_by=$user_id WHERE id = $id";
            $result = mysqli_query($this->conn, $query);

            return array("http" => 200, "data" => "Report is updated", "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in update reports", "status" => false);
        }
    }
}
