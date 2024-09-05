<?php

require_once 'enums/roles.enum.php';

class General
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getStates()
    {
        try {
            $query = "SELECT * FROM states_ref WHERE status =1 ";
            $result = mysqli_query($this->conn, $query);
            $states = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $states[] = $row;
            }

            return array("http" => 200, "data" => $states, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in states", "status" => false);
        }
    }

    public function getDistricts($data)
    {

        $requiredFields = array('state_code');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $stateCode = mysqli_real_escape_string($this->conn, $data['state_code']);

            $query = "SELECT * from districts_ref where status = 1 and state_code = $stateCode";
            $result = mysqli_query($this->conn, $query);
            $districts = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $districts[] = $row;
            }

            return array("http" => 200, "data" => $districts, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get districts", "status" => false);
        }
    }

    public function getTaluks($data)
    {
        $requiredFields = array('district_code');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $district_code = $data['district_code'];
            $query = "SELECT * from villages_ref where status= 1 and district_code = $district_code";
            $result = mysqli_query($this->conn, $query);
            $districts = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $districts[] = $row;
            }
            return array("http" => 200, "data" => $districts, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get taluks", "status" => false);
        }
    }

    public function getCategories()
    {
        try {
            $query = "SELECT id as value, name as label FROM categories WHERE status = 1 ";
            $result = mysqli_query($this->conn, $query);
            $categories = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = $row;
            }

            return array("http" => 200, "data" => $categories, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in catgeoires", "status" => false);
        }
    }
}
