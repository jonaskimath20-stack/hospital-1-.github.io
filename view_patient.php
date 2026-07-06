<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id <= 0) {
    redirect('manage_patients.php');
}

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    redirect('manage_patients.php');
}

$appointments = $conn->query("
    SELECT a.*, u.full_name as doctor_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    WHERE a.patient_id = $patient_id 
    ORDER BY a.appointment_date DESC
");

$bills = $conn->query("SELECT * FROM billing WHERE patient_id = $patient_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Patient Details</h1>
                <a href="manage_patients.php" class="btn-secondary" style="text-decoration:none;">← Back</a>
            </div>

            <div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
                <h3>Personal Information</h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px; margin-top:15px;">
                    <div><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
                    <div><strong>DOB:</strong> <?php echo date('d M Y', strtotime($patient['date_of_birth'])); ?></div>
                    <div><strong>Gender:</strong> <?php echo htmlspecialchars($patient['gender']); ?></div>
                    <div><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></div>
                    <div><strong>Email:</strong> <?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></div>
                    <div><strong>Emergency:</strong> <?php echo htmlspecialchars($patient['emergency_contact'] ?? 'N/A'); ?></div>
                    <div style="grid-column: span 2;"><strong>Address:</strong> <?php echo htmlspecialchars($patient['address'] ?? 'N/A'); ?></div>
                </div>
                <div style="margin-top:15px;">
                    <strong>Medical History:</strong>
                    <p style="background:#f8f9fa; padding:10px; border-radius:5px; margin-top:5px;">
                        <?php echo htmlspecialchars($patient['medical_history'] ?? 'None recorded'); ?>
                    </p>
                </div>
                <div style="margin-top:10px;">
                    <strong>Registered:</strong> <?php echo date('d M Y H:i', strtotime($patient['registration_date'])); ?>
                </div>
            </div>

            <div class="table-container" style="margin-bottom:20px;">
                <h3>Appointments</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Doctor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments && $appointments->num_rows > 0): ?>
                            <?php while ($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                    <td><span class="status status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No appointments</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <h3>Bills</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bills && $bills->num_rows > 0): ?>
                            <?php while ($row = $bills->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($row['payment_method'])); ?></td>
                                    <td><span class="status status-<?php echo htmlspecialchars($row['payment_status']); ?>"><?php echo ucfirst(htmlspecialchars($row['payment_status'])); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No bills</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>