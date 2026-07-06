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

// Get patients who have appointments with this doctor
$patients = $conn->query("
    SELECT DISTINCT p.*, 
           COUNT(a.id) as appointment_count,
           MAX(a.appointment_date) as last_visit
    FROM patients p 
    JOIN appointments a ON p.id = a.patient_id 
    WHERE a.doctor_id = $doctor_id 
    GROUP BY p.id 
    ORDER BY p.first_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>My Patients</h1>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Appointments</th>
                            <th>Last Visit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($patients && $patients->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $patients->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['date_of_birth'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo $row['appointment_count']; ?></td>
                                    <td><?php echo $row['last_visit'] ? date('d M Y', strtotime($row['last_visit'])) : 'Never'; ?></td>
                                    <td>
                                        <a href="view_patient.php?id=<?php echo $row['id']; ?>" class="btn-primary" style="padding:4px 10px;font-size:12px;text-decoration:none;">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No patients found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>