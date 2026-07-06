<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id <= 0) {
    redirect('manage_appointments.php');
}

// Use prepared statement
$stmt = $conn->prepare("
    SELECT a.*, p.first_name as p_first, p.last_name as p_last,
           u.full_name as d_name
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    WHERE a.id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appointment) {
    redirect('manage_appointments.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $status = sanitize($_POST['status']);
        $notes = sanitize($_POST['notes']);
        
        $stmt = $conn->prepare("UPDATE appointments SET status = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $notes, $appointment_id);
        
        if ($stmt->execute()) {
            $success = "Appointment updated successfully!";
            // Refresh appointment data
            $stmt2 = $conn->prepare("
                SELECT a.*, p.first_name as p_first, p.last_name as p_last,
                       u.full_name as d_name
                FROM appointments a 
                JOIN patients p ON a.patient_id = p.id 
                JOIN doctors d ON a.doctor_id = d.id 
                JOIN users u ON d.user_id = u.id 
                WHERE a.id = ?
            ");
            $stmt2->bind_param("i", $appointment_id);
            $stmt2->execute();
            $appointment = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $error = "Failed to update appointment.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Edit Appointment</h1>
                <a href="manage_appointments.php" class="btn-secondary" style="text-decoration:none;">← Back</a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
                <h3>Appointment Details</h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px; margin-top:15px;">
                    <div><strong>Patient:</strong> <?php echo htmlspecialchars($appointment['p_first'] . ' ' . $appointment['p_last']); ?></div>
                    <div><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment['d_name']); ?></div>
                    <div><strong>Date:</strong> <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></div>
                    <div><strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></div>
                </div>
            </div>

            <form method="POST" style="background:white; padding:25px; border-radius:10px;">
                <?php echo csrf_field(); ?>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="scheduled" <?php echo $appointment['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="no-show" <?php echo $appointment['status'] === 'no-show' ? 'selected' : ''; ?>>No-Show</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Notes</label>
                        <textarea name="notes" rows="3"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn-primary">Update Appointment</button>
                    <a href="manage_appointments.php" class="btn-secondary" style="text-decoration:none;padding:10px 20px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>