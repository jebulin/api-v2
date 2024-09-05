<?php

require_once 'enums/roles.enum.php';

require_once 'enums/approval.enum.php';

class NewsLetter
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createNewsLetter($data)
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        if ($role_id != Roles::SUPER_ADMIN && $role_id != Roles::ADMIN) {
            return array("http" => 403, "msg" => "Unauthorized", "status" => false);
        }

        $requiredFields = array('heading', 'content');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $heading = mysqli_real_escape_string($this->conn, $data['heading']);
            $content = mysqli_real_escape_string($this->conn, $data['content']);

            $query = "INSERT INTO news_letters (user_id, heading, content, created_by)
                VALUES ('$user_id','$heading', '$content', $user_id); ";
            $result = mysqli_query($this->conn, $query);

            return array("http" => 200, "msg" => "News letter is created: $heading", "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get create newsletters", "status" => false);
        }
    }

    public function getAllNewsLetters($data)
    {

        $requiredFields = array('limit', 'offset'); 

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $limit = mysqli_real_escape_string($this->conn, $data['limit']);
            $offset = mysqli_real_escape_string($this->conn, $data['offset']);
            
            $query = "SELECT * from news_letters where status = 1 order by id desc limit $limit offset $offset";
            $result = mysqli_query($this->conn, $query);
            $newsLetters = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $newsLetters[] = $row;
            }

            return array("http" => 200, "data" => $newsLetters, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get all news letters", "status" => false);
        }
    }

    public function getOneNewsLetter($data)
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
            $query = "SELECT * from news_letters where status= 1 and id = $id";
            $result = mysqli_query($this->conn, $query);
            $newsLetters = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $newsLetters = $row;
            }
            return array("http" => 200, "data" => $newsLetters, "status" => true);
        } catch (Exception $e) {
            error_log($e);
            return array("http" => 500, "msg" => "Internal server error in get one newsletters", "status" => false);
        }
    }

    public function updateNewsLetter($data)
    {
        $user_id =  $_SESSION['id'];
        $role_id =  $_SESSION['role_id'];

        if ($role_id != Roles::SUPER_ADMIN && $role_id != Roles::ADMIN) {
            return array("http" => 403, "msg" => "Unauthorized", "status" => false);
        }

        $requiredFields = array('id', 'heading', 'content', 'status');

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $heading = mysqli_real_escape_string($this->conn, $data['heading']);
            $content = mysqli_real_escape_string($this->conn, $data['content']);
            $status = mysqli_real_escape_string($this->conn, $data['status']);
            $id = mysqli_real_escape_string($this->conn, $data['id']);

            $query = "UPDATE news_letters SET heading = '$heading', content='$content', updated_by=$user_id, status=$status WHERE id = $id";
            $result = mysqli_query($this->conn, $query);

            return array("http" => 200, "data" => "NewsLetter is updated: $heading", "status" => true);
        } catch (Exception $e) {
            error_log($e);;
            return array("http" => 500, "msg" => "Internal server error in update newsletters", "status" => false);
        }
    }
}
