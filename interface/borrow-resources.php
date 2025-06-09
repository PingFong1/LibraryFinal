<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';
require_once '../controller/ResourceController.php';

Session::start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../login.php");
    exit();
}

$borrowingController = new BorrowingController();
$resourceController = new ResourceController();

// Handle borrowing request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $result = $borrowingController->borrowResource($_SESSION['user_id'], $_POST['resource_id']);
    $message = $result['message'];
    $success = $result['success'];
}

// Get resource type from GET parameter, default to books
$resourceType = isset($_GET['type']) ? $_GET['type'] : 'books';

// Get available resources based on type
switch ($resourceType) {
    case 'media':
        $availableResources = $borrowingController->getAvailableAndPendingMedia($_SESSION['user_id']);
        $columns = ['title', 'category', 'accession_number', 'media_type', 'runtime', 'format'];
        $defaultIcon = 'bi-film';
        break;
    case 'periodicals':
        $availableResources = $borrowingController->getAvailableAndPendingPeriodicals($_SESSION['user_id']);
        $columns = ['title', 'category', 'accession_number', 'publication_date', 'volume', 'issue'];
        $defaultIcon = 'bi-journal';
        break;
    default: // books
        $availableResources = $borrowingController->getAvailableAndPendingBooks($_SESSION['user_id']);
        $columns = ['title', 'category', 'author', 'isbn', 'publisher', 'accession_number'];
        $defaultIcon = 'bi-book';
}

// Search functionality
$searchTerm = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
if (!empty($searchTerm)) {
    $availableResources = array_filter($availableResources, function($resource) use ($searchTerm, $columns) {
        foreach ($columns as $column) {
            if (isset($resource[$column]) && 
                strpos(strtolower($resource[$column]), $searchTerm) !== false) {
                return true;
            }
        }
        return false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/resources.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
  
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="borrowing-monitoring-container">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Available Resources</h2>
                    <div class="d-flex align-items-center">
                        <form method="GET" class="d-flex" id="resourceForm">
                            <select name="type" class="form-select me-2" style="width: 150px;" onchange="this.form.submit()">
                                <option value="books" <?php echo $resourceType == 'books' ? 'selected' : ''; ?>>Books</option>  ``  Q`1 aQ!~
                                <option value="periodicals" <?php echo $resourceType == 'periodicals' ? 'selected' : ''; ?>>Periodicals</option>
                                <option value="media" <?php echo $resourceType == 'media' ? 'selected' : ''; ?>>Media</option>
                                
                            </select>
                            
                            <input type="text" name="search" class="form-control me-2" placeholder="Search..." 
                                   value="<?php echo htmlspecialchars($searchTerm); ?>"
                                   style="width: 200px;">
                            
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if (isset($message)): ?>
                    <div class="alert <?php echo $result ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Resources Table -->
                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($availableResources as $resource): ?>
                    <div class="col">
                        <div class="card h-100">
                            <!-- Add cursor-pointer class and onclick to open drawer -->
                            <div class="card-content cursor-pointer" onclick="showResourceDetails(<?php echo htmlspecialchars(json_encode($resource)); ?>)">
                                <!-- Cover Image -->
                                <div class="card-img-top text-center p-3" style="height: 200px;">
                                    <?php if (!empty($resource['cover_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($resource['cover_image']); ?>" 
                                             alt="Cover" 
                                             class="h-100"
                                             style="object-fit: contain;"
                                             onerror="this.onerror=null; this.src='assets/images/default.png';">
                                    <?php else: ?>
                                        <img src="assets/images/default.png" 
                                             alt="Default Cover" 
                                             class="h-100"
                                             style="object-fit: contain;">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h6>
                                    <p class="card-text">
                                        <?php if (isset($resource['author'])): ?>
                                            <small class="text-muted">By <?php echo htmlspecialchars($resource['author']); ?></small><br>
                                        <?php endif; ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($resource['category']); ?></span>
                                    </p>
                                </div>
                            </div>
                            
                        <!-- Replace the existing card-footer div with this: -->
                            <div class="card-footer bg-transparent border-0 p-3">
                                <?php if (isset($resource['pending']) && $resource['pending']): ?>
                                    <button class="btn btn-warning w-100" disabled>
                                        <i class="bi bi-hourglass-split"></i> Pending Approval
                                    </button>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="resource_id" value="<?php echo $resource['resource_id']; ?>">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle"></i> Request
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($availableResources)): ?>
                    <div class="alert alert-info text-center">
                        No resources available in this category.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Resource Details Drawer -->
    <div class="offcanvas offcanvas-end" tabindex="-1"id="resourceDrawer" aria-labelledby="resourceDrawerLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="resourceDrawerLabel">Resource Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="resource-details">
                <div class="text-center mb-4">
                    <img id="drawerCoverImage" src="" alt="Cover" class="img-fluid mb-3" style="max-height: 300px;">
                </div>
                <h3 id="drawerTitle" class="mb-3"></h3>
                <div id="drawerDetails" class="mb-4"></div>
                
                <form method="POST" id="drawerBorrowForm">
                    <input type="hidden" name="resource_id" id="drawerResourceId">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Borrow
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="assets/js/resources.js"></script>
</body>
</html>
