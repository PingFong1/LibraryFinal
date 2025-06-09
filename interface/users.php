<?php
require_once '../controller/UserController.php';
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

Session::start();
if (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')) {
    header("Location: dashboard.php");
    exit();
}

$userController = new UserController();
$users = $userController->getUsers();

// Add search functionality
$searchQuery = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
if ($searchQuery) {
    $users = array_filter($users, function($user) use ($searchQuery) {
        $searchLower = strtolower($searchQuery);
        return strpos(strtolower($user['username']), $searchLower) !== false ||
               strpos(strtolower($user['first_name'] . ' ' . $user['last_name']), $searchLower) !== false ||
               strpos(strtolower($user['membership_id']), $searchLower) !== false ||
               strpos((string)$user['user_id'], $searchQuery) !== false;
    });
}

// Define role configurations
$roleConfig = [
    'admin' => ['max_books' => 10],
    'faculty' => ['max_books' => 5],
    'staff' => ['max_books' => 4],
    'student' => ['max_books' => 3]
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only allow admin to delete users
    if (isset($_POST['delete_user'])) {
        if ($_SESSION['role'] !== 'admin') {
            Session::setFlash('error', 'Only administrators can delete users');
            header("Location: users.php");
            exit();
        }
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        
        // Prevent deleting own account
        if ($userId == $_SESSION['user_id']) {
            Session::setFlash('error', 'You cannot delete your own account');
            header("Location: users.php");
            exit();
        }
        
        if ($userController->deleteUser($userId)) {
            Session::setFlash('success', 'User deleted successfully');
        } else {
            Session::setFlash('error', 'Error deleting user. The user might be the last admin or have associated records.');
        }
        header("Location: users.php");
        exit();
    }
    // For create/update, check if user is admin for updates
    else {
        if (isset($_POST['user_id']) && !empty($_POST['user_id']) && $_SESSION['role'] !== 'admin') {
            Session::setFlash('error', 'Only administrators can edit users');
            header("Location: users.php");
            exit();
        }
        // Sanitize input
        $userData = [
            'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING),
            'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING),
            'last_name' => filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'role' => filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING),
            'max_books' => filter_input(INPUT_POST, 'max_books', FILTER_SANITIZE_NUMBER_INT),
            'borrowing_days_limit' => filter_input(INPUT_POST, 'borrowing_days_limit', FILTER_SANITIZE_NUMBER_INT) ?? 7
        ];

        // Validate borrowing_days_limit
        if (!is_numeric($userData['borrowing_days_limit']) || $userData['borrowing_days_limit'] < 1) {
            $userData['borrowing_days_limit'] = 7; // Set default if invalid
        }

        // Validate email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Invalid email format');
            header("Location: users.php");
            exit();
        } else {
            // Handle password
            if (!empty($_POST['password'])) {
                $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                // Update existing user
                $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
                if ($userController->updateUser($userId, $userData)) {
                    Session::setFlash('success', 'User updated successfully');
                    header("Location: users.php");
                    exit();
                } else {
                    Session::setFlash('error', 'Error updating user');
                    header("Location: users.php");
                    exit();
                }
            } else {
                // Create new user
                if (empty($_POST['password'])) {
                    Session::setFlash('error', 'Password is required for new users');
                    header("Location: users.php");
                    exit();
                } else if ($userController->createUser($userData)) {
                    Session::setFlash('success', 'User created successfully');
                    header("Location: users.php");
                    exit();
                } else {
                    Session::setFlash('error', 'Error creating user');
                    header("Location: users.php");
                    exit();
                }
            }
        }
    }
}

// Get flash messages
$success_message = Session::getFlash('success');
$error_message = Session::getFlash('error');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
    <style>
        .borrowing-monitoring-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .page-header {
            background-color: #003161;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebarModal.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="borrowing-monitoring-container">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">User Management</h2>
                    <div class="d-flex align-items-center">
                        <form class="me-3" method="GET" action="">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       placeholder="Search users" 
                                       name="search"
                                       value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                <?php if ($searchQuery): ?>
                                    <a href="users.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                     
                        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#userModal">
                                <i class="bi bi-plus-lg"></i> Add New
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Membership ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Max Books</th>
                                <th>Borrowing Days</th>
                                <th>Borrowings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): 
                                // Get user's borrowings
                                $borrowingController = new BorrowingController();
                                $userBorrowings = $borrowingController->getUserBorrowingHistory($user['user_id']);
                                $activeBorrowings = array_filter($userBorrowings, function($borrowing) {
                                    return $borrowing['status'] === 'active';
                                });
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['membership_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                <td><?php echo htmlspecialchars($user['max_books']); ?></td>
                                <td><?php echo htmlspecialchars($user['borrowing_days_limit']); ?> days</td>
                                <td>
                                    <?php 
                                    $borrowingController = new BorrowingController();
                                    $userBorrowings = $borrowingController->getUserBorrowingHistory($user['user_id']);
                                    $activeBorrowings = array_filter($userBorrowings, function($borrowing) {
                                        return $borrowing['status'] === 'active';
                                    });
                                    
                                    // Show button regardless of active borrowings
                                    ?>
                                    <button class="btn btn-sm text-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#borrowingsModal<?php echo $user['user_id']; ?>">
                                        <i class="bi bi-book"></i> 
                                        View <?php echo !empty($activeBorrowings) ? '(' . count($activeBorrowings) . ')' : ''; ?>
                                    </button>

                                    <!-- Borrowings Modal -->
                                    <div class="modal fade" id="borrowingsModal<?php echo $user['user_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Borrowing Details for <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Nav tabs -->
                                                    <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#active<?php echo $user['user_id']; ?>">
                                                                Active Borrowings
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#history<?php echo $user['user_id']; ?>">
                                                                Borrowing History
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <!-- Tab content -->
                                                    <div class="tab-content mt-3">
                                                        <!-- Active Borrowings Tab -->
                                                        <div id="active<?php echo $user['user_id']; ?>" class="tab-pane active">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Title</th>
                                                                            <th>Borrow Date</th>
                                                                            <th>Due Date</th>
                                                                            <th>Status</th>
                                                                            <th>Fine Amount</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($activeBorrowings as $borrowing): ?>
                                                                            <tr>
                                                                                <td><?php echo htmlspecialchars($borrowing['title']); ?></td>
                                                                                <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                                                                <td><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></td>
                                                                                <td>
                                                                                    <span class="badge <?php 
                                                                                        echo $borrowing['status'] === 'overdue' ? 'bg-danger' : 
                                                                                            ($borrowing['status'] === 'active' ? 'bg-warning' : 'bg-success'); 
                                                                                    ?>">
                                                                                        <?php echo ucfirst(htmlspecialchars($borrowing['status'])); ?>
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <?php 
                                                                                    echo $borrowing['fine_amount'] > 0 
                                                                                        ? '$' . number_format($borrowing['fine_amount'], 2) 
                                                                                        : '-';
                                                                                    ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <!-- Borrowing History Tab -->
                                                        <div id="history<?php echo $user['user_id']; ?>" class="tab-pane fade">
                                                            <div class="table-responsive">
                                                                <table class="table table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Title</th>
                                                                            <th>Borrow Date</th>
                                                                            <th>Due Date</th>
                                                                            <th>Return Date</th>
                                                                            <th>Status</th>
                                                                            <th>Fine Amount</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($userBorrowings as $borrowing): ?>
                                                                            <tr>
                                                                                <td><?php echo htmlspecialchars($borrowing['title']); ?></td>
                                                                                <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                                                                <td><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></td>
                                                                                <td>
                                                                                    <?php echo $borrowing['return_date'] 
                                                                                        ? date('M d, Y', strtotime($borrowing['return_date'])) 
                                                                                        : 'Not returned'; ?>
                                                                                </td>
                                                                                <td>
                                                                                    <span class="badge <?php 
                                                                                        echo $borrowing['status'] === 'overdue' ? 'bg-danger' : 
                                                                                            ($borrowing['status'] === 'active' ? 'bg-warning' : 'bg-success'); 
                                                                                    ?>">
                                                                                        <?php echo ucfirst(htmlspecialchars($borrowing['status'])); ?>
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <?php 
                                                                                    echo $borrowing['fine_amount'] > 0 
                                                                                        ? '$' . number_format($borrowing['fine_amount'], 2) 
                                                                                        : '-';
                                                                                    ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <button class="btn btn-sm edit-user text-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#userModal"
                                                data-user='<?php echo htmlspecialchars(json_encode($user)); ?>'>
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <?php if ($user['user_id'] != $_SESSION['user_id'] && ($user['role'] !== 'admin' || count($users) > 1)): ?>
                                            <form method="POST" class="d-inline delete-user-form">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                                <input type="hidden" name="delete_user" value="1">
                                                <button type="submit" class="btn btn-sm text-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Modal (for both Add and Edit) -->
            <div class="modal fade" id="userModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">User Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" id="userForm" class="needs-validation" novalidate>
                            <div class="modal-body">
                                <input type="hidden" name="user_id" id="user_id">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" id="username" required
                                           pattern="[a-zA-Z0-9._-]{3,}" title="Username must be at least 3 characters and can only contain letters, numbers, dots, underscores, and hyphens">
                                    <div class="invalid-feedback">
                                        Please provide a valid username.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" id="password" minlength="6">
                                    <small class="text-muted">Minimum 6 characters. Leave empty to keep current password when editing</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="role" id="role" required>
                                        <option value="admin">Admin</option>
                                        <option value="faculty">Faculty</option>
                                        <option value="staff">Staff</option>
                                        <option value="student">Student</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Max Books</label>
                                    <input type="number" class="form-control" name="max_books" id="max_books" 
                                           required min="1" max="50">
                                    <small class="text-muted">Default values: Admin (10), Faculty (5), Staff (4), Student (3)</small>
                                </div>
                                <div class="mb-3">
                                    <label for="borrowing_days_limit" class="form-label">Borrowing Days Limit</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="borrowing_days_limit" 
                                           name="borrowing_days_limit" 
                                           min="1" 
                                           max="30" 
                                           value="7"
                                           required>
                                    <div class="form-text">Maximum number of days a user can borrow resources</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/user-management.js"></script>
    <script>
        // Add this to your existing JavaScript that handles the edit user modal
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...

    // When role changes, update default max_books
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

    // When editing user, populate max_books
    const editButtons = document.querySelectorAll('.edit-user');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userData = JSON.parse(this.dataset.user);
            document.getElementById('max_books').value = userData.max_books;
            document.getElementById('borrowing_days_limit').value = 
                userData.borrowing_days_limit || 7; // Default to 7 if not set
        });
    });
});
    </script>
</body>
</html>