/**
 * Hospital Management System - JavaScript
 */

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                    showError(field, 'This field is required');
                } else {
                    field.style.borderColor = '';
                    removeError(field);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Real-time validation on input
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.hasAttribute('required') && this.value.trim()) {
                this.style.borderColor = '#2ecc71';
                removeError(this);
            }
        });
        
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.style.borderColor = '#e74c3c';
                showError(this, 'This field is required');
            }
        });
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn, [onclick*="confirm"]');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Toggle password visibility
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    togglePasswordBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });

    // Date input - set min date to today for appointments
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (input.name === 'date' || input.id === 'date') {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
        }
    });

    // Print functionality
    const printBtn = document.querySelector('.print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const tableRows = document.querySelectorAll('table tbody tr');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Status filter
    const statusFilter = document.querySelector('.status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            
            tableRows.forEach(function(row) {
                const statusCell = row.querySelector('.status');
                if (statusCell) {
                    const status = statusCell.textContent.toLowerCase();
                    if (!selectedStatus || status === selectedStatus) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    }

    // Mobile sidebar toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Tooltip initialization
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(function(element) {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            tooltip.style.opacity = '1';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });

    // Auto-save form data (for draft functionality)
    const autoSaveForms = document.querySelectorAll('.auto-save');
    autoSaveForms.forEach(function(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            input.addEventListener('change', function() {
                saveFormData(form);
            });
        });
    });

    // Load saved form data
    autoSaveForms.forEach(function(form) {
        loadFormData(form);
    });

    // Chart initialization (if charts exist)
    initializeCharts();

    // Data table with pagination
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(function(table) {
        const rows = table.querySelectorAll('tbody tr');
        if (rows.length > 10) {
            setupPagination(table, rows);
        }
    });
});

// Helper Functions

function showError(field, message) {
    let error = field.parentElement.querySelector('.error-message');
    if (!error) {
        error = document.createElement('span');
        error.className = 'error-message';
        error.style.cssText = 'color: #e74c3c; font-size: 12px; display: block; margin-top: 5px;';
        field.parentElement.appendChild(error);
    }
    error.textContent = message;
}

function removeError(field) {
    const error = field.parentElement.querySelector('.error-message');
    if (error) {
        error.remove();
    }
}

function saveFormData(form) {
    const formId = form.id || 'form_' + Math.random().toString(36).substr(2, 9);
    const data = {};
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(function(input) {
        data[input.name] = input.value;
    });
    
    localStorage.setItem('form_' + formId, JSON.stringify(data));
}

function loadFormData(form) {
    const formId = form.id || 'form_' + Math.random().toString(36).substr(2, 9);
    const saved = localStorage.getItem('form_' + formId);
    
    if (saved) {
        const data = JSON.parse(saved);
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(function(input) {
            if (data[input.name]) {
                input.value = data[input.name];
            }
        });
    }
}

function setupPagination(table, rows) {
    const perPage = 10;
    const totalPages = Math.ceil(rows.length / perPage);
    let currentPage = 1;
    
    // Create pagination controls
    const paginationDiv = document.createElement('div');
    paginationDiv.className = 'pagination';
    paginationDiv.style.cssText = 'display: flex; justify-content: center; gap: 10px; margin-top: 20px;';
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Previous';
    prevBtn.className = 'btn-secondary';
    prevBtn.style.cssText = 'padding: 5px 15px;';
    prevBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });
    paginationDiv.appendChild(prevBtn);
    
    // Page numbers
    const pageInfo = document.createElement('span');
    pageInfo.style.cssText = 'display: flex; align-items: center;';
    paginationDiv.appendChild(pageInfo);
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next';
    nextBtn.className = 'btn-secondary';
    nextBtn.style.cssText = 'padding: 5px 15px;';
    nextBtn.addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
        }
    });
    paginationDiv.appendChild(nextBtn);
    
    table.parentElement.appendChild(paginationDiv);
    
    function showPage(page) {
        const start = (page - 1) * perPage;
        const end = start + perPage;
        
        rows.forEach(function(row, index) {
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        pageInfo.textContent = `Page ${page} of ${totalPages}`;
        currentPage = page;
    }
    
    showPage(1);
}

function initializeCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        const chartContainers = document.querySelectorAll('.chart-container');
        chartContainers.forEach(function(container) {
            const ctx = container.querySelector('canvas');
            if (ctx) {
                const config = JSON.parse(container.getAttribute('data-chart-config') || '{}');
                new Chart(ctx, config);
            }
        });
    }
}

// AJAX Functions

function fetchData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (callback) callback(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function postData(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (callback) callback(data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Formatting Functions

function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Export functions for use in other scripts
window.hms = {
    formatCurrency: formatCurrency,
    formatDate: formatDate,
    formatDateTime: formatDateTime,
    fetchData: fetchData,
    postData: postData
};