<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// Get all patients with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Count total
$count_result = $conn->query("SELECT COUNT(*) as total FROM patients");
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

$patients = $conn->query("
    SELECT p.*, COUNT(a.id) as appointment_count 
    FROM patients p 
    LEFT JOIN appointments a ON p.id = a.patient_id 
    GROUP BY p.id 
    ORDER BY p.id DESC
    LIMIT $offset, $per_page
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Manage Patients</h1>
                <!-- BUTTON IMETOLEWA - Admin anaona tu -->
                <!-- <a href="../receptionist/register_patient.php" class="btn-primary" style="text-decoration:none;">+ Register Patient</a> -->
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Appointments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($patients && $patients->num_rows > 0): ?>
                            <?php $i = $offset + 1; while ($row = $patients->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['date_of_birth'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo $row['appointment_count']; ?></td>
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