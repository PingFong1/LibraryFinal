<?php
require_once '../controller/BookController.php';
require_once '../controller/Session.php';

Session::start();
Session::requireAdmin();

$bookController = new BookController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Delete
    if (isset($_POST['delete_book'])) {
        $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_SANITIZE_NUMBER_INT);
        try {
            if ($bookController->deleteBook($resourceId)) {
                Session::setFlash('success', 'Book deleted successfully');
            }
        } catch (Exception $e) {
            Session::setFlash('error', $e->getMessage());
        }
        header("Location: books.php");
        exit();
    }
    // Handle Create/Update
    else {
        // Sanitize and validate input
        $bookData = [
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'author' => filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING),
            'isbn' => filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_STRING),
            'publisher' => filter_input(INPUT_POST, 'publisher', FILTER_SANITIZE_STRING),
            'edition' => filter_input(INPUT_POST, 'edition', FILTER_SANITIZE_STRING),
            'publication_date' => filter_input(INPUT_POST, 'publication_date', FILTER_SANITIZE_STRING),
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
        ];

        // Generate Accession Number if not provided
        $bookData['accession_number'] = filter_input(INPUT_POST, 'accession_number', FILTER_SANITIZE_STRING);
        if (empty($bookData['accession_number'])) {
            $bookData['accession_number'] = $bookController->generateAccessionNumber();
        }

        // Update or Create Book
        if (isset($_POST['resource_id']) && !empty($_POST['resource_id'])) {
            $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_SANITIZE_NUMBER_INT);
            if ($bookController->updateBook($resourceId, $bookData)) {
                Session::setFlash('success', 'Book updated successfully');
                header("Location: books.php");
                exit();
            } else {
                Session::setFlash('error', 'Error updating book');
                header("Location: books.php");
                exit();
            }
        } else {
            if ($bookController->createBook($bookData)) {
                Session::setFlash('success', 'Book created successfully');
                header("Location: books.php");
                exit();
            } else {
                Session::setFlash('error', 'Error creating book');
                header("Location: books.php");
                exit();
            }
        }
    }
}

// Get books for display
$books = $bookController->getBooks();

// Get flash messages
$success_message = Session::getFlash('success');
$error_message = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                    <h2 class="mb-0">Book Management</h2>
                    <div class="d-flex align-items-center">
                        <div class="box p-3 border rounded me-3">
                            <span>Total Books: <?php echo count($books); ?></span>
                        </div>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#bookModal">
                            <i class="bi bi-plus-lg"></i> Add New
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Cover Image</th>
                                <th>Accession Number</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($book['cover_image'])): ?>
                                        <img src="../uploads/covers/<?php echo htmlspecialchars(basename($book['cover_image'])); ?>" 
                                             alt="Cover" 
                                             style="width: 50px; height: 70px; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='assets/images/default-cover.png';">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 70px;">
                                            <i class="bi bi-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($book['accession_number']); ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td>
                                    <span class="badge 
                                    <?php 
                                    echo $book['status'] === 'available' ? 'bg-success' : 'bg-warning'; 
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($book['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm text-warning edit-book" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bookModal"
                                            data-book='<?php echo htmlspecialchars(json_encode($book)); ?>'>
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-info print-book"
                                            onclick="printBook(<?php echo htmlspecialchars(json_encode($book)); ?>)">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <input type="hidden" name="resource_id" value="<?php echo $book['resource_id']; ?>">
                                        <input type="hidden" name="delete_book" value="1">
                                        <button type="submit" class="btn btn-sm text-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Book Details Offcanvas -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="bookDetailsDrawer" aria-labelledby="bookDetailsLabel" style="width: 600px;">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="bookDetailsLabel">Book Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="text-center mb-4">
                            <div id="drawerCoverImage" class="mb-3">
                                <!-- Cover image will be inserted here -->
                            </div>
                        </div>
                        <div class="book-info">
                            <h3 id="drawerTitle" class="mb-3"></h3>
                            
                            <div class="mb-4">
                                <span id="drawerStatus" class="badge"></span>
                                <span id="drawerCategory" class="badge bg-secondary ms-2"></span>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted">Author</small>
                                        <p id="drawerAuthor" class="mb-2"></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted">ISBN</small>
                                        <p id="drawerIsbn" class="mb-2"></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted">Publisher</small>
                                        <p id="drawerPublisher" class="mb-2"></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted">Edition</small>
                                        <p id="drawerEdition" class="mb-2"></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted">Publication Date</small>
                                        <p id="drawerPublicationDate" class="mb-2"></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <small class="text-muted">Accession Number</small>
                                        <p id="drawerAccessionNumber" class="mb-2"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button class="btn btn-warning edit-from-drawer" type="button">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form method="POST" class="d-inline delete-from-drawer" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                    <input type="hidden" name="resource_id" id="drawerResourceId">
                                    <input type="hidden" name="delete_book" value="1">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book Modal (Create/Edit) -->
            <div class="modal fade" id="bookModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Book Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="resource_id" id="resource_id">
                                
                                <div class="mb-3">
                                    <label class="form-label">Cover Image</label>
                                    <input type="file" class="form-control" name="cover_image" id="cover_image" accept="image/*">
                                </div>
                                
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
                                        <input type="text" class="form-control" name="isbn" id="isbn">
                                    </div>
                                    <!-- <div class="mb-3">
                                        <label for="accession_number" class="form-label">Accession Number</label>
                                        <input type="text" class="form-control" id="accession_number" name="accession_number" readonly>
                                    </div> -->
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Publisher</label>
                                        <input type="text" class="form-control" name="publisher" id="publisher">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Edition</label>
                                        <input type="text" class="form-control" name="edition" id="edition">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Publication Date</label>
                                        <input type="date" class="form-control" name="publication_date" id="publication_date">
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Book</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/resources.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookModal = document.getElementById('bookModal');
            const editBookButtons = document.querySelectorAll('.edit-book');

            editBookButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const book = JSON.parse(this.getAttribute('data-book'));
                    
                    // Populate modal fields
                    document.getElementById('resource_id').value = book.resource_id;
                    document.getElementById('title').value = book.title;
                    document.getElementById('author').value = book.author;
                    document.getElementById('isbn').value = book.isbn;
                    document.getElementById('publisher').value = book.publisher;
                    document.getElementById('edition').value = book.edition;
                    document.getElementById('publication_date').value = book.publication_date;
                    document.getElementById('category').value = book.category;
                    document.getElementById('accession_number').value = book.accession_number;
                });
            });

            // Handle adding new book to reset/generate accession number
            const addNewBookButton = document.querySelector('button[data-bs-target="#bookModal"]');
            addNewBookButton.addEventListener('click', function() {
                document.getElementById('accession_number').value = ''; // Clear for auto-generation
            });
        });

        function printBook(book) {
            // Create print window content
            const printContent = `
                <html>
                <head>
                    <title>Book Details - ${book.title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .book-details { max-width: 800px; margin: 20px auto; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .detail-row { margin-bottom: 15px; }
                        .label { font-weight: bold; }
                        .cover-image { max-width: 200px; margin: 20px auto; display: block; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="book-details">
                        <div class="header">
                            <h2>Book Details</h2>
                            <p>Generated on ${new Date().toLocaleDateString()}</p>
                        </div>
                       
                        <div class="detail-row">
                            <span class="label">Title:</span> ${book.title}
                        </div>
                        <div class="detail-row">
                            <span class="label">Author:</span> ${book.author}
                        </div>
                        <div class="detail-row">
                            <span class="label">ISBN:</span> ${book.isbn}
                        </div>
                        <div class="detail-row">
                            <span class="label">Publisher:</span> ${book.publisher}
                        </div>
                        <div class="detail-row">
                            <span class="label">Edition:</span> ${book.edition}
                        </div>
                        <div class="detail-row">
                            <span class="label">Publication Date:</span> ${book.publication_date}
                        </div>
                        <div class="detail-row">
                            <span class="label">Accession Number:</span> ${book.accession_number}
                        </div>
                        <div class="detail-row">
                            <span class="label">Category:</span> ${book.category}
                        </div>
                       
                    </div>
                    <div class="no-print" style="text-align: center; margin-top: 20px;">
                        <button onclick="window.print()">Print</button>
                        <button onclick="window.close()">Close</button>
                    </div>
                </body>
                </html>
            `;

            // Open new window and write content
            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
        }
    </script>
</body>
</html>