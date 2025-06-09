document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 3 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 3000);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Clear form when adding new user
    document.querySelector('[data-bs-target="#userModal"]').addEventListener('click', function() {
        const form = document.getElementById('userForm');
        form.reset();
        form.classList.remove('was-validated');
        document.getElementById('user_id').value = '';
        document.getElementById('password').required = true;
        document.querySelector('.modal-title').textContent = 'Add New User';
    });

    // Fill form when editing user
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const form = document.getElementById('userForm');
            form.classList.remove('was-validated');
            document.getElementById('password').required = false;
            
            const user = JSON.parse(this.dataset.user);
            document.getElementById('user_id').value = user.user_id;
            document.getElementById('username').value = user.username;
            document.getElementById('password').value = ''; // Clear password field
            document.getElementById('first_name').value = user.first_name;
            document.getElementById('last_name').value = user.last_name;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('max_books').value = user.max_books;
            document.querySelector('.modal-title').textContent = 'Edit User';
        });
    });

    // Add role change handler for max_books defaults
    document.getElementById('role').addEventListener('change', function() {
        const roleDefaults = {
            'admin': 10,
            'faculty': 5,
            'staff': 4,
            'student': 3
        };
        const maxBooksInput = document.getElementById('max_books');
        // Only set default if the field is empty or when creating new user
        if (!document.getElementById('user_id').value || !maxBooksInput.value) {
            maxBooksInput.value = roleDefaults[this.value] || 3;
        }
    });

    // Confirm delete
    document.querySelectorAll('form[onsubmit]').forEach(form => {
        form.onsubmit = function(e) {
            return confirm('Are you sure you want to delete this user?');
        };
    });
});