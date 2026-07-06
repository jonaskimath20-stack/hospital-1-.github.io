<aside class="sidebar">
    <div class="logo">
        🏥 <span>HMS</span>
    </div>
    <nav>
        <ul>
            <?php if (hasRole('admin')): ?>
                <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 <span>Dashboard</span></a></li>
                <li><a href="manage_doctors.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_doctors') !== false || strpos($_SERVER['PHP_SELF'], 'edit_doctor') !== false ? 'active' : ''; ?>">👨‍⚕️ <span>Doctors</span></a></li>
                <li><a href="manage_patients.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_patients') !== false || strpos($_SERVER['PHP_SELF'], 'view_patient') !== false ? 'active' : ''; ?>">🧑‍🤝‍🧑 <span>Patients</span></a></li>
                <li><a href="manage_staff.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_staff') !== false ? 'active' : ''; ?>">👩‍💼 <span>Staff</span></a></li>
                <li><a href="manage_appointments.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_appointments') !== false || strpos($_SERVER['PHP_SELF'], 'edit_appointment') !== false ? 'active' : ''; ?>">📅 <span>Appointments</span></a></li>
            <?php elseif (hasRole('doctor')): ?>
                <li><a href="../doctor/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 <span>Dashboard</span></a></li>
                <li><a href="../doctor/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">📅 <span>Appointments</span></a></li>
                <li><a href="../doctor/patients.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' || basename($_SERVER['PHP_SELF']) == 'view_patient.php' ? 'active' : ''; ?>">🧑‍🤝‍🧑 <span>Patients</span></a></li>
            <?php elseif (hasRole('receptionist')): ?>
                <li><a href="../receptionist/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 <span>Dashboard</span></a></li>
                <li><a href="../receptionist/register_patient.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register_patient.php' ? 'active' : ''; ?>">➕ <span>Register Patient</span></a></li>
                <li><a href="../receptionist/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">📅 <span>Appointments</span></a></li>
                <li><a href="../receptionist/billing.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>">💰 <span>Billing</span></a></li>
            <?php endif; ?>
            
            <li><a href="../logout.php">🚪 <span>Logout</span></a></li>
        </ul>
    </nav>
</aside>