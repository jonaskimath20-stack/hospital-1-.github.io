<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle Add Doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
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
        if (empty($username) || empty($password) || empty($full_name) || empty($email) || empty($specialization) || empty($license_number) || $experience < 0 || $fee < 0) {
            $error = "Please fill in all required fields with valid values.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'doctor')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $phone);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Insert doctor
                $sql2 = "INSERT INTO doctors (user_id, specialization, license_number, experience_years, consultation_fee, available_days, available_time) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("issidss", $user_id, $specialization, $license_number, $experience, $fee, $days, $time);
                $stmt2->execute();
                $stmt2->close();
                
                $success = "Doctor added successfully!";
            } else {
                $error = "Failed to add doctor. Username or email may already exist.";
            }
            $stmt->close();
        }
    }
}

// Handle Delete Doctor (using prepared statement)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get user_id first
    $stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $stmt->close();
        
        // Delete doctor and user
        $conn->query("DELETE FROM doctors WHERE id = $id");
        $conn->query("DELETE FROM users WHERE id = $user_id");
    }
    redirect('manage_doctors.php');
}

// Get all doctors
$doctors = $conn->query("
    SELECT d.*, u.full_name, u.email, u.phone, u.username 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    ORDER BY d.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Manage Doctors</h1>
                <button class="btn-primary" onclick="document.getElementById('addDoctorForm').style.display='block'">+ Add Doctor</button>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Add Doctor Form -->
            <div id="addDoctorForm" style="display:none; background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h3>Add New Doctor</h3>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone">
                        </div>
                        <div class="form-group">
                            <label>Specialization *</label>
                            <input type="text" name="specialization" required>
                        </div>
                        <div class="form-group">
                            <label>License Number *</label>
                            <input type="text" name="license_number" required>
                        </div>
                        <div class="form-group">
                            <label>Experience (Years) *</label>
                            <input type="number" name="experience" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>Consultation Fee *</label>
                            <input type="number" step="0.01" name="fee" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>Available Days</label>
                            <input type="text" name="days" placeholder="e.g. Mon-Wed-Fri">
                        </div>
                        <div class="form-group">
                            <label>Available Time</label>
                            <input type="text" name="time" placeholder="e.g. 09:00-17:00">
                        </div>
                    </div>
                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <button type="submit" name="add_doctor" class="btn-success">Save Doctor</button>
                        <button type="button" class="btn-secondary" onclick="document.getElementById('addDoctorForm').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Doctors List -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Specialization</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Fee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($doctors && $doctors->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $doctors->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td>$<?php echo number_format($row['consultation_fee'], 2); ?></td>
                                    <td>
                                        <a href="edit_doctor.php?id=<?php echo $row['id']; ?>" class="btn-warning" style="padding:4px 10px;font-size:12px;text-decoration:none;">Edit</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-danger" style="padding:4px 10px;font-size:12px;text-decoration:none;" onclick="return confirm('Are you sure you want to delete this doctor?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No doctors found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>