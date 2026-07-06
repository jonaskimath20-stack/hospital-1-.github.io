<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('doctor')) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$doctor = $conn->query("
    SELECT id FROM doctors WHERE user_id = $user_id
")->fetch_assoc();
$doctor_id = $doctor['id'];

// Filter by status
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$where = "a.doctor_id = $doctor_id";
if ($status_filter && in_array($status_filter, ['scheduled', 'completed', 'cancelled', 'no-show'])) {
    $where .= " AND a.status = '$status_filter'";
}

$appointments = $conn->query("
    SELECT a.*, p.first_name, p.last_name, p.phone, p.email
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    WHERE $where 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>My Appointments</h1>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="?status=scheduled" class="btn-primary" style="text-decoration:none;padding:8px 15px;font-size:14px;">Scheduled</a>
                    <a href="?status=completed" class="btn-success" style="text-decoration:none;padding:8px 15px;font-size:14px;">Completed</a>
                    <a href="?status=cancelled" class="btn-danger" style="text-decoration:none;padding:8px 15px;font-size:14px;">Cancelled</a>
                    <a href="?" class="btn-secondary" style="text-decoration:none;padding:8px 15px;font-size:14px;">All</a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments && $appointments->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td><span class="status status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                    <td>
                                        <a href="view_patient.php?id=<?php echo $row['patient_id']; ?>" class="btn-primary" style="padding:4px 10px;font-size:12px;text-decoration:none;">View</a>
                                        <?php if ($row['status'] === 'scheduled'): ?>
                                            <a href="update_status.php?appointment=<?php echo $row['id']; ?>&status=completed" class="btn-success" style="padding:4px 10px;font-size:12px;text-decoration:none;" onclick="return confirm('Mark as completed?')">Complete</a>
                                            <a href="update_status.php?appointment=<?php echo $row['id']; ?>&status=cancelled" class="btn-danger" style="padding:4px 10px;font-size:12px;text-decoration:none;" onclick="return confirm('Cancel this appointment?')">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>