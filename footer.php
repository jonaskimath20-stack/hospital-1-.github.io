<!-- Footer -->
    <footer class="footer" style="text-align: center; padding: 20px; margin-top: 30px; color: #7f8c8d; font-size: 14px; border-top: 1px solid #ecf0f1;">
        <p>&copy; <?php echo date('Y'); ?> Hospital Management System. All rights reserved.</p>
        <p>Developed by Group 2 - Bachelor in Cyber Security</p>
    </footer>
    <!-- JavaScript -->
    <script src="<?php echo BASE_URL; ?>js/script.js"></script>
    
    <!-- Additional JavaScript -->
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo BASE_URL . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>