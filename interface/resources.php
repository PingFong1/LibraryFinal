<?php
require_once '../controller/ResourceController.php';
require_once '../controller/Session.php';

Session::start();
Session::requireAdmin();

$resourceController = new ResourceController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check the action type
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    if ($action === 'add') {
        // Generate unique accession number
        $accessionNumber = $resourceController->generateAccessionNumber();

        // Prepare book data
        $bookData = [
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'accession_number' => $accessionNumber,
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
            'author' => filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING),
            'isbn' => filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_STRING),
            'publisher' => filter_input(INPUT_POST, 'publisher', FILTER_SANITIZE_STRING),
            'edition' => filter_input(INPUT_POST, 'edition', FILTER_SANITIZE_STRING),
            'publication_date' => filter_input(INPUT_POST, 'publication_date', FILTER_SANITIZE_STRING)
        ];

        // Create resource
        if ($resourceController->createResource($bookData)) {
            Session::setFlash('success', 'Book added successfully. Accession Number: ' . $accessionNumber);
            header("Location: resources.php");
            exit();
        } else {
            Session::setFlash('error', 'Error adding book');
            header("Location: resources.php");
            exit();
        }
    } elseif ($action === 'edit') {
        $resource_id = filter_input(INPUT_POST, 'resource_id', FILTER_VALIDATE_INT);
        
        // Prepare book data
        $bookData = [
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
            'author' => filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING),
            'isbn' => filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_STRING),
            'publisher' => filter_input(INPUT_POST, 'publisher', FILTER_SANITIZE_STRING),
            'edition' => filter_input(INPUT_POST, 'edition', FILTER_SANITIZE_STRING),
            'publication_date' => filter_input(INPUT_POST, 'publication_date', FILTER_SANITIZE_STRING)
        ];

        // Update resource
        if ($resourceController->updateResource($resource_id, $bookData)) {
            Session::setFlash('success', 'Book updated successfully');
            header("Location: resources.php");
            exit();
        } else {
            Session::setFlash('error', 'Error updating book');
            header("Location: resources.php");
            exit();
        }
    } elseif ($action === 'delete') {
        $resource_id = filter_input(INPUT_POST, 'resource_id', FILTER_VALIDATE_INT);
        
        // Delete resource
        if ($resourceController->deleteResource($resource_id)) {
            Session::setFlash('success', 'Book deleted successfully');
            header("Location: resources.php");
            exit();
        } else {
            Session::setFlash('error', 'Error deleting book');
            header("Location: resources.php");
            exit();
        }
    }
}

// Get existing resources
$resources = $resourceController->getResources();

// Get flash messages
$success_message = Session::getFlash('success');
$error_message = Session::getFlash('error');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources Management - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebarModal.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Resources Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
                    <i class="bi bi-plus-lg"></i> Add New Book
                </button>
            </div>
            <hr>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Accession Number</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>ISBN</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resource['accession_number']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['author']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['category']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['isbn']); ?></td>
                                    <td>
                                        <span class="badge 
                                        <?php 
                                        echo $resource['status'] === 'available' ? 'bg-success' : 'bg-warning'; 
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($resource['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary edit-book" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#bookModal" 
                                                    data-id="<?php echo $resource['resource_id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($resource['title']); ?>"
                                                    data-author="<?php echo htmlspecialchars($resource['author']); ?>"
                                                    data-isbn="<?php echo htmlspecialchars($resource['isbn']); ?>"
                                                    data-category="<?php echo htmlspecialchars($resource['category']); ?>"
                                                    data-publisher="<?php echo htmlspecialchars($resource['publisher']); ?>"
                                                    data-edition="<?php echo htmlspecialchars($resource['edition']); ?>"
                                                    data-publication-date="<?php echo htmlspecialchars($resource['publication_date']); ?>"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-book" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal" 
                                                    data-id="<?php echo $resource['resource_id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($resource['title']); ?>"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
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

    <!-- Book Modal (Add/Edit) -->
    <div class="modal fade" id="bookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="action" id="form-action" value="add">
                    <input type="hidden" name="resource_id" id="resource-id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author</label>
                                <input type="text" class="form-control" name="author" id="author" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ISBN</label>
                                <input type="text" class="form-control" name="isbn" id="isbn" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" id="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Fiction">Fiction</option>
                                    <option value="Non-Fiction">Non-Fiction</option>
                                    <option value="Reference">Reference</option>
                                    <option value="Academic">Academic</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Publisher</label>
                                <input type="text" class="form-control" name="publisher" id="publisher" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Edition</label>
                                <input type="text" class="form-control" name="edition" id="edition">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Publication Date</label>
                            <input type="date" class="form-control" name="publication_date" id="publication_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="modal-submit-btn">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="resource_id" id="delete-resource-id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete the book: <strong id="delete-book-title"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/resources.js"></script>