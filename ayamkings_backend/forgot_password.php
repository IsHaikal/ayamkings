<?php
// forgot_password.php - Send password reset email
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_config.php';

// Email configuration (from environment variables)
$SMTP_EMAIL = getenv('SMTP_EMAIL') ?: 'haikaliskandar735@gmail.com';
$SMTP_PASSWORD = getenv('SMTP_PASSWORD') ?: '';
$FRONTEND_URL = getenv('FRONTEND_URL') ?: 'https://ayamkings.vercel.app';

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    $conn = getDbConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Don't reveal if email exists or not for security
        $response['success'] = true;
        $response['message'] = 'If this email exists, a reset link has been sent.';
        echo json_encode($response);
        exit();
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store token in database (delete any existing tokens for this user first)
    $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $deleteStmt->bind_param("s", $email);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    $insertStmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $email, $token, $expiry);
    
    if (!$insertStmt->execute()) {
        throw new Exception('Failed to create reset token.');
    }
    $insertStmt->close();
    
    // Create reset link
    $resetLink = $FRONTEND_URL . '/reset_password.html?token=' . $token;
    
    // Send email using Gmail SMTP
    $to = $email;
    $subject = "AyamKings - Password Reset Request";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #F59E0B, #D97706); padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .header h1 { color: white; margin: 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #F59E0B; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍗 AyamKings</h1>
            </div>
            <div class='content'>
                <h2>Password Reset Request</h2>
                <p>Hello <strong>{$user['full_name']}</strong>,</p>
                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' class='button'>Reset My Password</a>
                </p>
                <p>This link will expire in <strong>1 hour</strong>.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>Best regards,<br>AyamKings Team</p>
            </div>
            <div class='footer'>
                <p>&copy; 2024 AyamKings. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try to send email (may fail on Railway due to SMTP restrictions)
    $emailSent = @sendEmailViaSMTP($to, $subject, $message, $SMTP_EMAIL, $SMTP_PASSWORD);
    
    // Always return success with reset link (for testing/demo purposes)
    // In production, remove reset_link from response
    $response['success'] = true;
    $response['message'] = 'Password reset link generated successfully.';
    $response['reset_link'] = $resetLink; // Remove this line in production
    
    if (!$emailSent) {
        $response['email_note'] = 'Email could not be sent. Use the link below.';
        error_log("[Forgot Password] Email failed to send to: " . $to);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("[Forgot Password Error] " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

// Function to send email via SMTP (Gmail)
function sendEmailViaSMTP($to, $subject, $htmlBody, $fromEmail, $appPassword) {
    $smtpServer = 'ssl://smtp.gmail.com';
    $smtpPort = 465;
    
    // Build email content
    $boundary = md5(time());
    $headers = "From: AyamKings <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Try using mail() first (works if server configured)
    if (@mail($to, $subject, $htmlBody, $headers)) {
        return true;
    }
    
    // Fallback: Use fsockopen for direct SMTP
    $errno = 0;
    $errstr = '';
    $socket = @fsockopen($smtpServer, $smtpPort, $errno, $errstr, 30);
    
    if (!$socket) {
        error_log("SMTP Connection failed: $errstr ($errno)");
        return false;
    }
    
    // Read greeting
    fgets($socket, 512);
    
    // EHLO
    fputs($socket, "EHLO ayamkings.vercel.app\r\n");
    while ($line = fgets($socket, 512)) {
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket, 512);
    
    fputs($socket, base64_encode($fromEmail) . "\r\n");
    fgets($socket, 512);
    
    fputs($socket, base64_encode(str_replace(' ', '', $appPassword)) . "\r\n");
    $authResponse = fgets($socket, 512);
    
    if (strpos($authResponse, '235') === false) {
        error_log("SMTP Auth failed: " . $authResponse);
        fclose($socket);
        return false;
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM:<{$fromEmail}>\r\n");
    fgets($socket, 512);
    
    // RCPT TO
    fputs($socket, "RCPT TO:<{$to}>\r\n");
    fgets($socket, 512);
    
    // DATA
    fputs($socket, "DATA\r\n");
    fgets($socket, 512);
    
    // Message
    $message = "To: {$to}\r\n";
    $message .= "From: AyamKings <{$fromEmail}>\r\n";
    $message .= "Subject: {$subject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "\r\n";
    $message .= $htmlBody;
    $message .= "\r\n.\r\n";
    
    fputs($socket, $message);
    $dataResponse = fgets($socket, 512);
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return strpos($dataResponse, '250') !== false;
}
?>
