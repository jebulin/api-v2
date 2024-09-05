<?php
// require 'vendor/autoload.php';

// use PHPMailer\PHPMailer\PHPMailer;

// use PHPMailer\PHPMailer\SMTP;

// define("MAILHOST", "smtp.gmail.com");

// define("USERNAME", "thinangroups@gmail.com");

// define("PASSWORD", "kran zalr dclf zssv");

// define('SEND_FROM',"lorryapp@blueontech.com");

// define('SEND_FROM_NAME',"blueontech.com");

// define('REPLY_TO',"thinangroups@gmail.com");

// define('REPLY_TO_NAME',"BLUE ON TECH");





function sendMail($email, $subject, $message)
{

  // $mail = new PHPMailer(true);

  // $mail->isSMTP();

  // $mail->SMTPAuth = true;

  // $mail->Host = MAILHOST;

  // $mail->Username = USERNAME;

  // $mail->Password = PASSWORD;

  // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

  // $mail->Port= 587;

  // $mail->setFrom(SEND_FROM, SEND_FROM_NAME);

  // $mail->addAddress($email);

  // $mail->addReplyTo(REPLY_TO, REPLY_TO_NAME);

  // $mail->IsHTML(true);

  // $mail->Subject=$subject;

  // $mail->Body= $message;

  // $mail->AltBody= $message;

  // if(!$mail->send()){
  //   return "Email not send";
  // }else{
  //   return "success";
  // }

  try {
    $to = $email;
    $headers = "From: Evahan";
    mail($to, $subject, $message, $headers);
    return "success";
  } catch (Exception $e) {
    return "Email not send";
  }
}
