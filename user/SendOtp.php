<?php

include '../dbconnect.php';
include '../../configuration.php';
include('smtp/PHPMailerAutoload.php');
 function smtp_mailer($to,$subject, $msg){
    $mail = new PHPMailer(); 
    $mail->IsSMTP(); 
    $mail->SMTPAuth = true; 
    $mail->SMTPSecure = 'tls'; 
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587; 
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    //$mail->SMTPDebug = 2; 
    $mail->Username = "chaitanyabagade59@gmail.com";
    $mail->Password = "kkog qgca jzcu ixig";
    $mail->SetFrom("chaitanyabagade59@gmail.com");
    $mail->Subject = $subject;
    $mail->Body =$msg;
    $mail->AddAddress($to);
    $mail->SMTPOptions=array('ssl'=>array(
       'verify_peer'=>false,
       'verify_peer_name'=>false,
       'allow_self_signed'=>false
    ));
    if(!$mail->Send()){
       return 'notsent';
    }else{
       return 'Sent';
    }
 }
// Check the connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "status_code" => 500,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Data to insert
$email = $_POST['email'];
$otp = rand(100000, 999999); // Generating a 6-digit OTP
$createdAt = date('Y-m-d H:i:s'); // Current timestamp
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Expiry time (10 minutes from now)

// Insert or update query
$sql = "INSERT INTO otp_table (email, otp, created_at, expires_at) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        otp = VALUES(otp), 
        created_at = VALUES(created_at), 
        expires_at = VALUES(expires_at)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(200); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "status_code" => 500,
        "message" => "Failed to prepare SQL statement."
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("ssss", $email, $otp, $createdAt, $expiresAt);

if ($stmt->execute()) {
    
      //// sending otp to email id///////////////////////////

      $html = '<html>
  <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f7fc;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
      <div style="background-color: #4CAF50; padding: 20px; text-align: center; color: #ffffff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        <h2 style="margin: 0; font-size: 24px;">FastFood - OTP Verification</h2>
        <p style="margin: 0; font-size: 14px;">One Time Password (OTP) for your account verification</p>
      </div>
      
      <div style="padding: 30px; color: #333333;">
        <p style="font-size: 16px; line-height: 1.5;">Hi there,</p>
        <p style="font-size: 16px; line-height: 1.5;">
          Thank you for signing up with FastFood! To complete your registration, please use the following One Time Password (OTP):
        </p>
        
        <div style="background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; color: #4CAF50; border-radius: 8px; margin: 20px 0;">
          <span style="letter-spacing: 2px;">'.$otp.'</span>
        </div>
        
        <p style="font-size: 16px; line-height: 1.5;">
          Please do not share this OTP with anyone. It is valid for the next 10 minutes.
        </p>

        <p style="font-size: 16px; line-height: 1.5;">
          If you did not request this, you can ignore this email.
        </p>
        
        <p style="font-size: 16px; line-height: 1.5;">
          Best Regards,<br>
          <strong>The FastFood Team</strong>
        </p>
      </div>

      <div style="background-color: #f4f7fc; padding: 15px; text-align: center; font-size: 12px; color: #888888;">
        <p style="margin: 0;">&copy; 2025 FastFood. All rights reserved.</p>
      </div>
    </div>
  </body>
</html>';


      smtp_mailer($email, 'OTP From FastFood', $html);
          /////////////////// ending email ///////////////
    
    http_response_code(201); // Created/Updated
    echo json_encode([
        "status" => "success",
        "status_code" => 201,
        "message" => "OTP generated and stored/updated successfully.",
        "data" => [
            "email" => $email,
            "expires_at" => $expiresAt
        ]
    ]);
} else {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "status_code" => 400,
        "message" => "Failed to store/update OTP: " . $stmt->error
    ]);
}

// Close the statement and connection
$stmt->close();
$conn->close();

?>
