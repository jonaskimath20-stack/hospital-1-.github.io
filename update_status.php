<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('doctor')) {
    redirect('../login.php');
}

$appointment_id = isset($_GET['appointment']) ? intval($_GET['appointment']) : 0;
$new_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

if ($appointment_id <= 0 || !in_array($new_status, ['scheduled', 'completed', 'cancelled', 'no-show'])) {
    redirect('appointments.php');
}

// Verify this appointment belongs to the doctor
$user_id = $_SESSION['user_id'];
$doctor = $conn->query("SELECT id FROM doctors WHERE user_id = $user_id")->fetch_assoc();
$doctor_id = $doctor['id'];

$check = $conn->query("SELECT id FROM appointments WHERE id = $appointment_id AND doctor_id = $doctor_id");
if ($check->num_rows === 0) {
    redirect('appointments.php');
}

// Update status
$stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $appointment_id);
$stmt->execute();
$stmt->close();

// If status is completed, add to billing if not already billed
if ($new_status === 'completed') {
    $appointment = $conn->query("SELECT patient_id, doctor_id FROM appointments WHERE id = $appointment_id")->fetch_assoc();
    $doctor_fee = $conn->query("SELECT consultation_fee FROM doctors WHERE id = {$appointment['doctor_id']}")->fetch_assoc();
    
    // Check if already billed
    $check_bill = $conn->query("SELECT id FROM billing WHERE appointment_id = $appointment_id");
    if ($check_bill->num_rows === 0) {
        $fee = $doctor_fee['consultation_fee'];
        $conn->query("
            INSERT INTO billing (patient_id, appointment_id, amount, payment_method, description) 
            VALUES ({$appointment['patient_id']}, $appointment_id, $fee, 'cash', 'Consultation fee')
        ");
    }
}

redirect('appointments.php' . ($new_status ? '?status=' . $new_status : ''));
?>