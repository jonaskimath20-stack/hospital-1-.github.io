<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// Get all appointments with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Count total
$count_result = $conn->query("SELECT COUNT(*) as total FROM appointments");
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

$appointments = $conn->query("
    SELECT a.*, 
           p.first_name as patient_first, p.last_name as patient_last,
           u.full_name as doctor_name, d.specialization
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT $offset, $per_page
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<body  class="bg-slate-700">
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Manage Appointments</h1>
                <!-- BUTTON IMETOLEWA - Admin anaona tu -->
                <!-- <a href="../receptionist/appointments.php" class="btn-primary" style="text-decoration:none;">+ Book Appointment</a> -->
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments && $appointments->num_rows > 0): ?>
                            <?php $i = $offset + 1; while ($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_first'] . ' ' . $row['patient_last']); ?></td>
                                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                    <td><span class="status status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                    <td>
                                        <a href="edit_appointment.php?id=<?php echo $row['id']; ?>" class="btn-warning" style="padding:4px 10px;font-size:12px;text-decoration:none;">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn-secondary" style="padding:5px 15px;text-decoration:none;">Previous</a>
                        <?php endif; ?>
                        <span style="display:flex;align-items:center;">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn-secondary" style="padding:5px 15px;text-decoration:none;">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>