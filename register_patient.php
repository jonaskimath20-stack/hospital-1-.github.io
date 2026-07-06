<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('receptionist')) {
    redirect('../login.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $dob = sanitize($_POST['dob']);
        $gender = sanitize($_POST['gender']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $emergency = sanitize($_POST['emergency']);
        $history = sanitize($_POST['history']);

        // Validate
        if (empty($first_name) || empty($last_name) || empty($dob) || empty($gender)) {
            $error = "Please fill in all required fields.";
        } else {
            $sql = "INSERT INTO patients (first_name, last_name, date_of_birth, gender, phone, email, address, emergency_contact, medical_history) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $first_name, $last_name, $dob, $gender, $phone, $email, $address, $emergency, $history);
            
            if ($stmt->execute()) {
                $success = "Patient registered successfully! ID: " . $conn->insert_id;
                // Reset form after successful submission
                $first_name = $last_name = $dob = $gender = $phone = $email = $address = $emergency = $history = '';
            } else {
                $error = "Failed to register patient. Please try again.";
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
    <title>Register Patient - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Register New Patient</h1>
                <a href="../admin/manage_patients.php" class="btn-secondary" style="text-decoration:none;">← Back to Patients</a>
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
                        <label>First Name *</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob" value="<?php echo htmlspecialchars($dob ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="">Select</option>
                            <option value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($gender) && $gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Address</label>
                        <textarea name="address" rows="2"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Emergency Contact</label>
                        <input type="text" name="emergency" value="<?php echo htmlspecialchars($emergency ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Medical History</label>
                        <textarea name="history" rows="2"><?php echo htmlspecialchars($history ?? ''); ?></textarea>
                    </div>
                </div>

                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn-success">Register Patient</button>
                    <a href="../admin/manage_patients.php" class="btn-secondary" style="text-decoration:none;padding:10px 20px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>