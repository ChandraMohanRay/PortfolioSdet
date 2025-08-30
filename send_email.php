<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Set recipient email address
    $to_email = "cmray1701@gmail.com";

    // Get form data
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $from_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    // Form validation
    if (empty($name) || empty($from_email) || empty($message) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Please fill out all required fields and provide a valid email address.";
        exit;
    }

    // Email headers
    $boundary = md5(time());
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: " . $name . " <" . $from_email . ">\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n\r\n";

    // Email body
    $email_body = "--" . $boundary . "\r\n";
    $email_body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
    $email_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $from_email . "\n";
    $email_body .= "Message:\n" . $message . "\n\n";

    // Handle file attachment
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['attachment']['name'];
        $file_tmp_name = $_FILES['attachment']['tmp_name'];
        $file_type = $_FILES['attachment']['type'];
        $file_size = $_FILES['attachment']['size'];

        $file = fopen($file_tmp_name, 'rb');
        $data = fread($file, $file_size);
        fclose($file);
        $data = chunk_split(base64_encode($data));

        $email_body .= "--" . $boundary . "\r\n";
        $email_body .= "Content-Type: " . $file_type . "; name=\"" . $file_name . "\"\r\n";
        $email_body .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\r\n";
        $email_body .= "Content-Transfer-Encoding: base64\r\n";
        $email_body .= "\r\n" . $data . "\r\n";
    }

    $email_body .= "--" . $boundary . "--\r\n";

    // Send the email
    $subject = "New Contact Form Submission from " . $name;
    if (mail($to_email, $subject, $email_body, $headers)) {
        http_response_code(200);
        echo "Thank you! Your message has been sent.";
    } else {
        http_response_code(500);
        echo "Oops! Something went wrong, and we couldn't send your message.";
    }

} else {
    http_response_code(405);
    echo "Method Not Allowed.";
}
?>
