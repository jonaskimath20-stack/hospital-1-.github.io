<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($doctor_id <= 0) {
    redirect('manage_doctors.php');
}

// Use prepared statement
$stmt = $conn->prepare("
    SELECT d.*, u.full_name, u.email, u.phone, u.username 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    WHERE d.id = ?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
    redirect('manage_doctors.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $specialization = sanitize($_POST['specialization']);
        $license_number = sanitize($_POST['license_number']);
        $experience = intval($_POST['experience']);
        $fee = floatval($_POST['fee']);
        $days = sanitize($_POST['days']);
        $time = sanitize($_POST['time']);
        
        // Validate
        if (empty($full_name) || empty($email) || empty($specialization) || empty($license_number) || $experience < 0 || $fee < 0) {
            $error = "Please fill in all required fields with valid values.";
        } else {
            // Update user
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $full_name, $email, $phone, $doctor['user_id']);
            
            if ($stmt->execute()) {
                // Update doctor
                $sql2 = "UPDATE doctors SET specialization = ?, license_number = ?, experience_years = ?, consultation_fee = ?, available_days = ?, available_time = ? WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("ssidssi", $specialization, $license_number, $experience, $fee, $days, $time, $doctor_id);
                $stmt2->execute();
                $stmt2->close();
                
                $success = "Doctor updated successfully!";
                
                // Refresh data
                $stmt3 = $conn->prepare("
                    SELECT d.*, u.full_name, u.email, u.phone, u.username 
                    FROM doctors d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.id = ?
                ");
                $stmt3->bind_param("i", $doctor_id);
                $stmt3->execute();
                $doctor = $stmt3->get_result()->fetch_assoc();
                $stmt3->close();
            } else {
                $error = "Failed to update doctor. Email may already exist.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Edit Doctor</h1>
                <a href="manage_doctors.php" class="btn-secondary" style="text-decoration:none;">← Back</a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" style="background:white; padding:25px; border-radius:10px;">
                <?php echo csrf_field(); ?>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($doctor['username']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Specialization *</label>
                        <input type="text" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>License Number *</label>
                        <input type="text" name="license_number" value="<?php echo htmlspecialchars($doctor['license_number']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Experience (Years) *</label>
                        <input type="number" name="experience" value="<?php echo htmlspecialchars($doctor['experience_years']); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Consultation Fee *</label>
                        <input type="number" step="0.01" name="fee" value="<?php echo htmlspecialchars($doctor['consultation_fee']); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Available Days</label>
                        <input type="text" name="days" value="<?php echo htmlspecialchars($doctor['available_days']); ?>" placeholder="e.g. Mon-Wed-Fri">
                    </div>
                    <div class="form-group">
                        <label>Available Time</label>
                        <input type="text" name="time" value="<?php echo htmlspecialchars($doctor['available_time']); ?>" placeholder="e.g. 09:00-17:00">
                    </div>
                </div>
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn-primary">Update Doctor</button>
                    <a href="manage_doctors.php" class="btn-secondary" style="text-decoration:none;padding:10px 20px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>