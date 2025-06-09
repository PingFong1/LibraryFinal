<?php
require_once '../controller/PeriodicalController.php';
require_once '../controller/Session.php';

Session::start();
Session::requireAdmin();

$periodicalController = new PeriodicalController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Delete
    if (isset($_POST['delete_periodical'])) {
        $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_SANITIZE_NUMBER_INT);
        try {
            if ($periodicalController->deletePeriodical($resourceId)) {
                Session::setFlash('success', 'Periodical deleted successfully');
            }
        } catch (Exception $e) {
            Session::setFlash('error', $e->getMessage());
        }
        header("Location: periodicals.php");
        exit();
    }
    // Handle Create/Update
    else {
        // Sanitize and validate input
        $periodicalData = [
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'issn' => filter_input(INPUT_POST, 'issn', FILTER_SANITIZE_STRING),
            'volume' => filter_input(INPUT_POST, 'volume', FILTER_SANITIZE_STRING),
            'issue' => filter_input(INPUT_POST, 'issue', FILTER_SANITIZE_STRING),
            'publication_date' => filter_input(INPUT_POST, 'publication_date', FILTER_SANITIZE_STRING),
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
        ];

        // Generate Accession Number if not provided
        $periodicalData['accession_number'] = filter_input(INPUT_POST, 'accession_number', FILTER_SANITIZE_STRING);
        if (empty($periodicalData['accession_number'])) {
            $periodicalData['accession_number'] = $periodicalController->generateAccessionNumber();
        }

        // Update or Create Periodical
        if (isset($_POST['resource_id']) && !empty($_POST['resource_id'])) {
            $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_SANITIZE_NUMBER_INT);
            if ($periodicalController->updatePeriodical($resourceId, $periodicalData)) {
                Session::setFlash('success', 'Periodical updated successfully');
                header("Location: periodicals.php");
                exit();
            } else {
                Session::setFlash('error', 'Error updating periodical');
                header("Location: periodicals.php");
                exit();
            }
        } else {
            if ($periodicalController->createPeriodical($periodicalData)) {
                Session::setFlash('success', 'Periodical created successfully');
                header("Location: periodicals.php");
                exit();
            } else {
                Session::setFlash('error', 'Error creating periodical');
                header("Location: periodicals.php");
                exit();
            }
        }
    }
}

// Get periodicals for display
$periodicals = $periodicalController->getPeriodicals();

// Get flash messages
$success_message = Session::getFlash('success');
$error_message = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periodicals Management - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
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
        
        <!-- Main Content -->
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
                    <h2 class="mb-0">
                        <i></i>Periodicals Management
                    </h2>
                    <div class="d-flex align-items-center">
                        <div class="box p-3 border rounded me-3">
                            <span>Total Periodicals: <?php echo count($periodicals); ?></span>
                        </div>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#periodicalModal">
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
                                <th>ISSN</th>
                                <th>Volume</th>
                                <th>Issue</th>
                                <th>Publication Date</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periodicals as $periodical): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($periodical['cover_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($periodical['cover_image']); ?>" 
                                             alt="Cover" 
                                             style="width: 50px; height: 70px; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='assets/images/default-cover.png';">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 70px;">
                                            <i class="bi bi-journal"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($periodical['accession_number']); ?></td>
                                <td><?php echo htmlspecialchars($periodical['title']); ?></td>
                                <td><?php echo htmlspecialchars($periodical['issn']); ?></td>
                                <td><?php echo htmlspecialchars($periodical['volume']); ?></td>
                                <td><?php echo htmlspecialchars($periodical['issue']); ?></td>
                                <td><?php echo htmlspecialchars($periodical['publication_date']); ?></td>
                                <td><?php echo htmlspecialchars($periodical['category']); ?></td>
                                <td>
                                    <span class="badge 
                                    <?php 
                                    echo $periodical['status'] === 'available' ? 'bg-success' : 'bg-warning'; 
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($periodical['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm text-warning edit-periodical" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#periodicalModal"
                                            data-periodical='<?php echo htmlspecialchars(json_encode($periodical)); ?>'>
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-info print-periodical"
                                            onclick="printPeriodical(<?php echo htmlspecialchars(json_encode($periodical)); ?>)">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this media resource?');">
                                        <input type="hidden" name="resource_id" value="<?php echo $periodical['resource_id']; ?>">
                                        <input type="hidden" name="delete_periodical" value="1">
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
            </div>
        </main>
    </div>

    <div class="modal fade" id="periodicalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Periodical Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="resource_id" id="resourceId">
                        <div class="mb-3">
                            <label class="form-label">Cover Image</label>
                            <input type="file" class="form-control" name="cover_image" id="cover_image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="issn" class="form-label">ISSN</label>
                            <input type="text" class="form-control" id="issn" name="issn" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="volume" class="form-label">Volume</label>
                                <input type="text" class="form-control" id="volume" name="volume">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="issue" class="form-label">Issue</label>
                                <input type="text" class="form-control" id="issue" name="issue">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="publication_date" class="form-label">Publication Date</label>
                            <input type="date" class="form-control" id="publication_date" name="publication_date">
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Academic Journal">Academic Journal</option>
                                <option value="Magazine">Magazine</option>
                                <option value="Newsletter">Newsletter</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Periodical</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/resources.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodicalModal = document.getElementById('periodicalModal');
            const periodicalForm = document.getElementById('periodicalForm');
            const editButtons = document.querySelectorAll('.edit-periodical');

            // Reset form when modal opens
            periodicalModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const periodicalData = button.getAttribute('data-periodical');

                if (periodicalData) {
                    const periodical = JSON.parse(periodicalData);
                    document.getElementById('resourceId').value = periodical.resource_id;
                    document.getElementById('title').value = periodical.title;
                    document.getElementById('issn').value = periodical.issn;
                    document.getElementById('volume').value = periodical.volume;
                    document.getElementById('issue').value = periodical.issue;
                    document.getElementById('publication_date').value = periodical.publication_date;
                    document.getElementById('category').value = periodical.category;
                    document.getElementById('accession_number').value = periodical.accession_number;
                } else {
                    periodicalForm.reset();
                    document.getElementById('resourceId').value = '';
                }
            });
        });

        function printPeriodical(periodical) {
            // Create print window content
            const printContent = `
                <html>
                <head>
                    <title>Periodical Details - ${periodical.title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .periodical-details { max-width: 800px; margin: 20px auto; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .detail-row { margin-bottom: 15px; }
                        .label { font-weight: bold; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="periodical-details">
                        <div class="header">
                            <h2>Periodical Details</h2>
                            <p>Generated on ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="detail-row">
                            <span class="label">Title:</span> ${periodical.title}
                        </div>
                        <div class="detail-row">
                            <span class="label">ISSN:</span> ${periodical.issn}
                        </div>
                        <div class="detail-row">
                            <span class="label">Volume:</span> ${periodical.volume}
                        </div>
                        <div class="detail-row">
                            <span class="label">Issue:</span> ${periodical.issue}
                        </div>
                        <div class="detail-row">
                            <span class="label">Publication Date:</span> ${periodical.publication_date}
                        </div>
                        <div class="detail-row">
                            <span class="label">Accession Number:</span> ${periodical.accession_number}
                        </div>
                        <div class="detail-row">
                            <span class="label">Category:</span> ${periodical.category}
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