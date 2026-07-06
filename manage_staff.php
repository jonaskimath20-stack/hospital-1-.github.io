<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $position = sanitize($_POST['position']);
        $department = sanitize($_POST['department']);
        $hire_date = sanitize($_POST['hire_date']);
        $salary = floatval($_POST['salary']);

        // Validate
        if (empty($username) || empty($password) || empty($full_name) || empty($email) || empty($position) || empty($hire_date)) {
            $error = "Please fill in all required fields.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'receptionist')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $phone);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $sql2 = "INSERT INTO staff (user_id, position, department, hire_date, salary) VALUES (?, ?, ?, ?, ?)";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("isssd", $user_id, $position, $department, $hire_date, $salary);
                $stmt2->execute();
                $stmt2->close();
                $success = "Staff member added successfully!";
            } else {
                $error = "Failed to add staff member. Username or email may already exist.";
            }
            $stmt->close();
        }
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT user_id FROM staff WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $stmt->close();
        $conn->query("DELETE FROM staff WHERE id = $id");
        $conn->query("DELETE FROM users WHERE id = $user_id");
    }
    redirect('manage_staff.php');
}

$staff = $conn->query("
    SELECT s.*, u.full_name, u.email, u.phone, u.username 
    FROM staff s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Manage Staff</h1>
                <button class="btn-primary" onclick="document.getElementById('addStaffForm').style.display='block'">+ Add Staff</button>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div id="addStaffForm" style="display:none; background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
                <h3>Add New Staff Member</h3>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group"><label>Username *</label><input type="text" name="username" required></div>
                        <div class="form-group"><label>Password *</label><input type="password" name="password" required minlength="6"></div>
                        <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required></div>
                        <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                        <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                        <div class="form-group"><label>Position *</label><input type="text" name="position" required></div>
                        <div class="form-group"><label>Department</label><input type="text" name="department"></div>
                        <div class="form-group"><label>Hire Date *</label><input type="date" name="hire_date" required></div>
                        <div class="form-group"><label>Salary</label><input type="number" step="0.01" name="salary" min="0"></div>
                    </div>
                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <button type="submit" name="add_staff" class="btn-success">Save Staff</button>
                        <button type="button" class="btn-secondary" onclick="document.getElementById('addStaffForm').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Hire Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($staff && $staff->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $staff->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['hire_date'])); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-danger" style="padding:4px 10px;font-size:12px;text-decoration:none;" onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No staff found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>