<?php
/**
 * Save Consultation Inquiry
 * HexaTP Consultation System
 */

header('Content-Type: application/json');

// Include database configuration
require_once 'db_config.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Sanitize and validate input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$appointment_date = trim($_POST['appointment_date'] ?? '');
$appointment_time = trim($_POST['appointment_time'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

if (empty($subject)) {
    $errors[] = 'Consultation type is required';
}

if (empty($appointment_date)) {
    $errors[] = 'Appointment date is required';
}

if (empty($appointment_time)) {
    $errors[] = 'Appointment time is required';
}

// Return validation errors
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ]);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO consultations (name, email, phone, consultation_type, appointment_date, appointment_time, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

// Bind parameters
$stmt->bind_param("sssssss", $name, $email, $phone, $subject, $appointment_date, $appointment_time, $message);

// Execute statement
if ($stmt->execute()) {
    // Also save to inquiries table for backward compatibility
    $stmt2 = $conn->prepare("INSERT INTO inquiries (name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $name, $email, $phone, $message);
    $stmt2->execute();
    $stmt2->close();

    // Send email notification
    $to = "connect@hexatp.com";
    $email_subject = "New Consultation Request: " . $subject;
    $email_body = "You have received a new consultation request.\n\n" .
                 "Name: $name\n" .
                 "Email: $email\n" .
                 "Phone: $phone\n" .
                 "Consultation Type: $subject\n" .
                 "Appointment Date: $appointment_date\n" .
                 "Appointment Time: $appointment_time\n" .
                 "Message: $message\n";
    $sender_email = "connect@hexatp.com";
    $headers = "From: HexaTP Website <$sender_email>\r\n" .
               "Reply-To: $email\r\n" .
               "X-Mailer: PHP/" . phpversion() . "\r\n" .
               "MIME-Version: 1.0\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($to, $email_subject, $email_body, $headers, "-f $sender_email");

    echo json_encode([
        'success' => true,
        'message' => 'Consultation request submitted successfully! We will contact you soon.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error submitting consultation: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
