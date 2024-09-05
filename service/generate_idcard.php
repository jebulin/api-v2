<?php

require_once 'enums/roles.enum.php';
require_once('tcpdf/tcpdf.php');

class IDCard
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function generateIdCard($data, $password)
    {
        $requiredFields = array("id");

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                // Throw an error if any required field is missing
                return array("http" => 400, "msg" => "Error: $field is missing in the input data.", "status" => false);
            }
        }

        try {
            $user_id = $data['id'];
            $query = "SELECT * from users where status = 1 and id = $user_id";
            $queryResult = mysqli_query($this->conn, $query);
            if ($result = $queryResult->fetch_assoc()) {
                // while ($row = $result->fetch_assoc()) {
                $pdf = new TCPDF();

                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);


                $pdf->SetMargins(0, 0, 0, true);

                $pdf->AddPage();

                $img_file = 'image/idcardfront2.jpg';
                $image_width = 179;
                $page_width = 210;
                $x_center = ($page_width - $image_width) / 2;
                $pdf->Image($img_file, $x_center, 0, $image_width, 297, '', '', '', false, 300, '', false, false, 0);

                $pdf->SetFont('Helvetica', 'B', 20);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetXY(30, 123);
                $pdf->Write(0, "Name: " . $result["first_name"] . " " . $result["last_name"]);
                $pdf->SetXY(30, 135);
                $pdf->Write(0, "Id No.: " . $result["id"]);
                $pdf->SetXY(30, 147);
                $pdf->Write(0, "Category: " . $result["category"]);
                $pdf->SetXY(30, 159);
                $pdf->Write(0, "District: " . $result["district"]);
                $pdf->SetXY(30, 171);
                $pdf->Write(0, "State: " . $result["state"]);
                $pdf->SetXY(30, 183);
                $pdf->Write(0, "Taluk: " . $result["taluk"]);
                $pdf->SetXY(30, 195);
                $pdf->Write(0, "Phone No: " . $result["phone_number"]);
                $pdf->SetXY(30, 207);
                $pdf->Write(0, "Email: ");
                $pdf->SetFont('Helvetica', 'B', 15);
                $pdf->SetXY(53, 209);
                $pdf->Write(0, $result["email"]);

                $pdf->AddPage();
                $img_file_back = 'image/idcardback1.jpg';
                $pdf->Image($img_file_back, $x_center, 0, $image_width, 297);

                $pdf_output = tempnam(sys_get_temp_dir(), 'id_card_');
                $pdf->Output($pdf_output, 'F');
                $pdf->close();

                ini_set('SMTP', 'smtp.example.com');
                ini_set('smtp_port', 587);

                $to = $result["email"];
                $subject = "Your ID Card";
                if($password ==  "null"){
                  $message = "Dear " . $result["first_name"] . " " . $result["last_name"] . ",\n\nPlease find attached your ID card.\n\nRegards,\nRegistration Team";  
                }else{
                    $message = "Dear " . $result["first_name"] . " " . $result["last_name"] . ",\n\nYour password is " . $password . "\n\nPlease find attached your ID card.\n\nRegards,\nRegistration Team";
}
                $boundary = md5(time());
                $headers = "From: noreply@bluontech.com\r\n";

                $headers .= "Bcc: evsbookingoffice@gmail.com\r\n";
                $headers .= "Bcc: rojarbluon@gmail.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
                $body = "--{$boundary}\r\n";
                $body .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
                $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
                $body .= $message . "\r\n\r\n";
                $file_content = file_get_contents($pdf_output);
                $file_content_base64 = chunk_split(base64_encode($file_content));
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: application/pdf; name=\"" . basename($pdf_output) . "\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"" . basename($pdf_output) . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $file_content_base64 . "\r\n\r\n";
                $body .= "--{$boundary}--";
                unlink($pdf_output);

                if (mail($to, $subject, $body, $headers)) {
                    return array("http" => 200, "msg" => 'Email sent', "status" => true);
                }

                return array("http" => 400, "msg" => 'Email not sent', "status" => false);

            }
            return array("http" => 404, "msg" => 'id not present', "status" => false);

            
        } catch (Exception $e) {
            return array("http" => 500, "msg" => "Id card not geenrated", "status" => false);
        }
    }
}
