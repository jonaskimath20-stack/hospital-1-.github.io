<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('receptionist')) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Get patients for dropdown
$patients = $conn->query("SELECT id, first_name, last_name FROM patients ORDER BY first_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $patient_id = intval($_POST['patient_id']);
        $appointment_id = !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null;
        $amount = floatval($_POST['amount']);
        $description = sanitize($_POST['description']);
        $payment_method = sanitize($_POST['payment_method']);

        // Validate
        if ($patient_id <= 0 || $amount <= 0 || empty($payment_method)) {
            $error = "Please fill in all required fields with valid values.";
        } else {
            if ($appointment_id) {
                $sql = "INSERT INTO billing (patient_id, appointment_id, amount, payment_method, description) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iidss", $patient_id, $appointment_id, $amount, $payment_method, $description);
            } else {
                $sql = "INSERT INTO billing (patient_id, amount, payment_method, description) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("idss", $patient_id, $amount, $payment_method, $description);
            }
            
            if ($stmt->execute()) {
                $success = "Bill created successfully!";
            } else {
                $error = "Failed to create bill. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Get recent bills
$bills = $conn->query("
    SELECT b.*, p.first_name, p.last_name 
    FROM billing b 
    JOIN patients p ON b.patient_id = p.id 
    ORDER BY b.created_at DESC 
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - HMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>
            
            <div class="page-header">
                <h1>Billing</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" style="background:white; padding:25px; border-radius:10px; margin-bottom:30px;">
                <?php echo csrf_field(); ?>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Patient *</label>
                        <select name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php while ($row = $patients->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Appointment ID (Optional)</label>
                        <input type="number" name="appointment_id" min="1" placeholder="Enter appointment ID">
                    </div>
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" step="0.01" name="amount" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="insurance">Insurance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Description</label>
                        <textarea name="description" rows="2" placeholder="Description of the bill"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-success">Create Bill</button>
            </form>

            <div class="table-container">
                <h3>Recent Bills</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bills && $bills->num_rows > 0): ?>
                            <?php $i = 1; while ($row = $bills->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($row['payment_method'])); ?></td>
                                    <td><span class="status status-<?php echo htmlspecialchars($row['payment_status']); ?>"><?php echo ucfirst(htmlspecialchars($row['payment_status'])); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No bills found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>