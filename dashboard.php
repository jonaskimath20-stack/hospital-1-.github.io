<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// Get statistics using prepared statements
$stats = [];

// Total patients
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM patients");
$stmt->execute();
$stats['patients'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total doctors
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM doctors");
$stmt->execute();
$stats['doctors'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total staff
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM staff");
$stmt->execute();
$stats['staff'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total appointments today
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()");
$stmt->execute();
$stats['appointments_today'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total appointments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments");
$stmt->execute();
$stats['appointments'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Recent appointments
$recent_appointments = $conn->query("
    SELECT a.*, p.first_name, p.last_name, d.specialization, u.full_name as doctor_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Dashboard</h1>
            </div>

            <div class="card-grid">
                <div class="card">
                    <div class="icon">👨‍⚕️</div>
                    <div class="number"><?php echo htmlspecialchars($stats['doctors']); ?></div>
                    <div class="label">Total Doctors</div>
                </div>
                <div class="card">
                    <div class="icon">🧑‍🤝‍🧑</div>
                    <div class="number"><?php echo htmlspecialchars($stats['patients']); ?></div>
                    <div class="label">Total Patients</div>
                </div>
                <div class="card">
                    <div class="icon">👩‍💼</div>
                    <div class="number"><?php echo htmlspecialchars($stats['staff']); ?></div>
                    <div class="label">Total Staff</div>
                </div>
                <div class="card">
                    <div class="icon">📅</div>
                    <div class="number"><?php echo htmlspecialchars($stats['appointments_today']); ?></div>
                    <div class="label">Today's Appointments</div>
                </div>
                <div class="card">
                    <div class="icon">📋</div>
                    <div class="number"><?php echo htmlspecialchars($stats['appointments']); ?></div>
                    <div class="label">Total Appointments</div>
                </div>
            </div>

            <div class="table-container">
                <h3>Recent Appointments</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_appointments && $recent_appointments->num_rows > 0): ?>
                            <?php while ($row = $recent_appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                    <td><span class="status status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>