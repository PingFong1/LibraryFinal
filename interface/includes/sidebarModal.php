<?php
require_once '../controller/Session.php';
Session::start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<div class="sidebar shadow-sm" style="min-height: 100vh; width: 250px;">
    <div class="p-4 d-flex flex-column h-100">
        <!-- Logo and Title Section -->
        <div class="d-flex align-items-center mb-4">
            <!-- <i class="bi bi-book fs-4 text-primary"></i> -->
            <h4 class="text-white ms-2 fw-bold">Library Management</h4>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="nav-menu flex-grow-1">
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="users.php">
                        <i class="bi bi-people"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <?php endif; ?>



                 <?php
                    // Ensure the user is logged in and check their role
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    ?>
                <li class="nav-item dropdown">
                    </a>
                    <ul class="dropdown-menu dropdown-custom" aria-labelledby="resourcesDropdown">
                        <!-- <li><a class="dropdown-item text-light" href="resources.php">All Resources</a></li> -->
                        <li><a class="dropdown-item text-light" href="books.php">Books</a></li>
                        <li><a class="dropdown-item text-light" href="periodicals.php">Periodicals</a></li>
                        <li><a class="dropdown-item text-light" href="media-resources.php">Media</a></li>
                    </ul>
                </li>
                <?php  } ?>

                <!-- Borrow books, periodicals and media section for student and faculty -->
                <?php
                    // Ensure the user is logged in and check their role
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' || $_SESSION['role'] === 'faculty') {
                    ?>
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3 dropdown-toggle" 
                    href="resources.php" id="resourcesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-book"></i>
                        <span>Borrow</span>
                    </a>
                    <ul class="dropdown-menu dropdown-custom" aria-labelledby="resourcesDropdown"> -->
                        <!-- <li><a class="dropdown-item text-light" href="resources.php">All Resources</a></li> -->
                        <!-- <li><a class="dropdown-item text-light" href="borrow-resources.php">Resources</a></li>
                        <li><a class="dropdown-item text-light" href="borrow-periodicals.php">Periodicals</a></li>
                        <li><a class="dropdown-item text-light" href="borrow-media.php">Media</a></li>
                    </ul>
                </li> -->
                <?php }?>
                     
                <!-- <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="borrowings.php">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Borrowings</span>
                    </a>
                </li>
                <?php endif; ?> -->

                <?php if ($_SESSION['role'] === 'student' || $_SESSION['role'] === 'faculty'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="borrow-resources.php">
                        <i class="bi bi-book"></i>
                        <span>Borrow Resources</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'student' || $_SESSION['role'] === 'faculty'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="borrowing_history.php">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Borrowing History</span>
                    </a>
                </li>
                <?php endif; ?>

                      <!-- Over due management section -->
                     <!-- Ensure the user is logged in and check their role -->

                 <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="audit.php">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Audit</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="generate-report.php">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span>Report</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="activity-logs.php">
                        <i class="bi bi-activity"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        
        <div class="mt-auto">
            <?php if ($_SESSION['role'] === 'student' || $_SESSION['role'] === 'faculty' || $_SESSION['role'] === 'staff'): ?>
            <a class="nav-link d-flex align-items-center gap-2 text-light rounded py-2 px-3" href="help-information.php">
                <i class="bi bi-question-circle"></i>
                <span>Help & Information</span>
            </a>
            <?php endif; ?>
            <a class="nav-link d-flex align-items-center gap-2 text-danger rounded py-2 px-3" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Help & Information Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">Help & Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- About the System Section -->
                <div class="mb-4">
                    <h6 class="fw-bold">About the System</h6>
                    <p>The Library Management System is designed to streamline the process of borrowing and managing library resources. It provides an efficient way to browse, borrow, and return books, periodicals, and media materials.</p>
                    
                    <h6 class="fw-bold mt-3">Key Features:</h6>
                    <ul>
                        <li>Easy resource browsing and searching</li>
                        <li>Online borrowing requests</li>
                        <li>Track borrowing history</li>
                        <li>View due dates and notifications</li>
                    </ul>
                </div>

                <!-- Developers Section -->
                <div>
                    <h6 class="fw-bold">Development Team</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">System Developers</h6>
                                    <ul class="list-unstyled">
                                        <li>Ebs - Lead Developer</li>
                                        <li>Ebs - UI/UX Designer</li>
                                        <li>Ebs - Backend Developer</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Contact Support</h6>
                                    <p>Email: mabuangNaSaLab@gmail.com<br>
                                    Phone: (123) 456-7890</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
    </div>
</div>

<!-- Update the Help & Information link to trigger the modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update the help link to trigger the modal
    const helpLink = document.querySelector('a[href="help-information.php"]');
    if (helpLink) {
        helpLink.setAttribute('href', '#');
        helpLink.setAttribute('data-bs-toggle', 'modal');
        helpLink.setAttribute('data-bs-target', '#helpModal');
    }
});
</script>

<style>
/* General Sidebar Styles */
.sidebar {
    background-color: #003161;
    width: 250px;
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* Ensure full height */
}

.nav-menu {
    display: flex;
    flex-direction: column;
    height: 100%; /* Take full height of parent */
}

.nav-menu .nav {
    display: flex;
    flex-direction: column;
    height: 100%; /* Take full height of parent */
}

/* Dropdown Menu Styles */
.dropdown-menu.dropdown-custom {
    background-color: #133E87 !important; /* Updated dropdown background color */
    border: none;
}

/* Dropdown Item Hover Styles */
.dropdown-menu .dropdown-item:hover {
    background-color: #2d5fa8 !important; /* Optional hover color for dropdown items */
    color: #fff; /* Optional text color */
}

/* Profile Section */
.user-profile {
    color: #f0f0f0;
}

/* Navbar Links */
.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #a0aec0;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.3s, color 0.3s;
}

.nav-link:hover {
    background-color: #2d3748;
    color: #edf2f7;
}

/* Active State (Optional, add logic for active menu) */
.nav-link.active {
    background-color: #4a5568;
    color: #edf2f7;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
    }
}

/* Add blur effect to modal backdrop */
.modal-backdrop.show {
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}

.modal-content {
    background-color: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.card {
    border: none;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
