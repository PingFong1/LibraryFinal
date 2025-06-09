<?php
require_once '../controller/MFAController.php';
$mfaController = new MFAController();
$mfa_enabled = $mfaController->isMFAEnabled($_SESSION['user_id']);
?>

<!-- Account Settings Modal -->
<div class="modal fade" id="accountSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Account Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Security Settings</h6>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <div>
                            <h6 class="mb-1">Two-Factor Authentication</h6>
                            <p class="text-muted mb-0 small">
                                <?php echo $mfa_enabled ? 'Enabled' : 'Not enabled'; ?>
                            </p>
                        </div>
                        <a href="mfa_settings.php" class="btn btn-sm <?php echo $mfa_enabled ? 'btn-outline-primary' : 'btn-primary'; ?>">
                            <?php echo $mfa_enabled ? 'Manage' : 'Enable'; ?>
                        </a>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Change Password</h6>
                    <form id="changePasswordForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>

                <div class="mb-4">
                    <h6 class="mb-3">Update Username</h6>
                    <form id="updateUsernameForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">New Username</label>
                            <input type="text" class="form-control" name="username" required 
                                   value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Username</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        alert('New passwords do not match');
        return;
    }
    
    fetch('../api/update_account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            current_password: formData.get('current_password'),
            password: formData.get('new_password')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password updated successfully');
            this.reset();
        } else {
            alert(data.message || 'Failed to update password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating password');
    });
});

document.getElementById('updateUsernameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('../api/update_account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: formData.get('username')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Username updated successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update username');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating username');
    });
});
</script> 