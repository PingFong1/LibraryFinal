<?php
require_once '../controller/MediaResourceController.php';
require_once '../controller/Session.php';

Session::start();
Session::requireAdmin();

$mediaController = new MediaResourceController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Delete
    if (isset($_POST['delete_media'])) {
        $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_SANITIZE_NUMBER_INT);
        try {
            if ($mediaController->deleteMediaResource($resourceId)) {
                Session::setFlash('success', 'Media resource deleted successfully');
            }
        } catch (Exception $e) {
            Session::setFlash('error', $e->getMessage());
        }
        header("Location: media-resources.php");
        exit();
    }
    // Handle Create/Update
    else {
        // Sanitize and validate input
        $mediaData = [
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'format' => filter_input(INPUT_POST, 'format', FILTER_SANITIZE_STRING),
            'runtime' => filter_input(INPUT_POST, 'runtime', FILTER_SANITIZE_NUMBER_INT),
            'media_type' => filter_input(INPUT_POST, 'media_type', FILTER_SANITIZE_STRING),
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
        ];

        // Generate Accession Number if not provided
        $mediaData['accession_number'] = filter_input(INPUT_POST, 'accession_number', FILTER_SANITIZE_STRING);
        if (empty($mediaData['accession_number'])) {
            $mediaData['accession_number'] = $mediaController->generateAccessionNumber();
        }

        // Update or Create Media Resource
        if (isset($_POST['resource_id']) && !empty($_POST['resource_id'])) {
            $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_SANITIZE_NUMBER_INT);
            if ($mediaController->updateMediaResource($resourceId, $mediaData)) {
                Session::setFlash('success', 'Media resource updated successfully');
                header("Location: media-resources.php");
                exit();
            } else {
                Session::setFlash('error', 'Error updating media resource');
                header("Location: media-resources.php");
                exit();
            }
        } else {
            if ($mediaController->createMediaResource($mediaData)) {
                Session::setFlash('success', 'Media resource created successfully');
                header("Location: media-resources.php");
                exit();
            } else {
                Session::setFlash('error', 'Error creating media resource');
                header("Location: media-resources.php");
                exit();
            }
        }
    }
}

// Get media resources for display
$mediaResources = $mediaController->getMediaResources();

// Get flash messages
$success_message = Session::getFlash('success');
$error_message = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Resources Management - Library Management System</title>
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
                        <i></i>Media Resources Management
                    </h2>
                    <div class="d-flex align-items-center">
                        <div class="box p-3 border rounded me-3">
                            <span>Total Media Resources: <?php echo count($mediaResources); ?></span>
                        </div>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#mediaModal">
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
                                <th>Format</th>
                                <th>Runtime</th>
                                <th>Media Type</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mediaResources as $media): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($media['cover_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($media['cover_image']); ?>" 
                                             alt="Cover" 
                                             style="width: 50px; height: 70px; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='assets/images/default-cover.png';">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 70px;">
                                            <i class="bi bi-camera-video"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($media['accession_number']); ?></td>
                                <td><?php echo htmlspecialchars($media['title']); ?></td>
                                <td><?php echo htmlspecialchars($media['format']); ?></td>
                                <td><?php echo htmlspecialchars($media['runtime']); ?> min</td>
                                <td><?php echo htmlspecialchars($media['media_type']); ?></td>
                                <td><?php echo htmlspecialchars($media['category']); ?></td>
                                <td>
                                    <span class="badge 
                                    <?php 
                                    echo $media['status'] === 'available' ? 'bg-success' : 'bg-warning'; 
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($media['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm text-warning edit-media" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#mediaModal"
                                            data-media='<?php echo htmlspecialchars(json_encode($media)); ?>'>
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm text-info print-media"
                                            onclick="printMedia(<?php echo htmlspecialchars(json_encode($media)); ?>)">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this media resource?');">
                                        <input type="hidden" name="resource_id" value="<?php echo $media['resource_id']; ?>">
                                        <input type="hidden" name="delete_media" value="1">
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

    <div class="modal fade" id="mediaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Media Resource Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="resource_id" id="resourceId">
                        
                        <!-- Add this new file input field -->
                        <div class="mb-3">
                            <label class="form-label">Cover Image</label>
                            <input type="file" class="form-control" name="cover_image" id="cover_image" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="format" class="form-label">Format</label>
                            <select class="form-select" id="format" name="format" required>
                                <option value="">Select Format</option>
                                <option value="DVD">DVD</option>
                                <option value="CD">CD</option>
                                <option value="Blu-ray">Blu-ray</option>
                                <option value="Digital">Digital</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="runtime" class="form-label">Runtime (minutes)</label>
                            <input type="number" class="form-control" id="runtime" name="runtime" required min="1">
                        </div>
                        <div class="mb-3">
                            <label for="media_type" class="form-label">Media Type</label>
                            <select class="form-select" id="media_type" name="media_type" required>
                                <option value="">Select Media Type</option>
                                <option value="Video">Video</option>
                                <option value="Audio">Audio</option>
                                <option value="Interactive">Interactive</option>
                                <option value="Educational">Educational</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Academic">Academic</option>
                                <option value="Documentary">Documentary</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Reference">Reference</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Media Resource</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/resources.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle edit button clicks
            const editButtons = document.querySelectorAll('.edit-media');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const mediaData = JSON.parse(this.dataset.media);
                    
                    // Populate form fields
                    document.getElementById('resourceId').value = mediaData.resource_id;
                    document.getElementById('title').value = mediaData.title;
                    document.getElementById('format').value = mediaData.format;
                    document.getElementById('runtime').value = mediaData.runtime;
                    document.getElementById('media_type').value = mediaData.media_type;
                    document.getElementById('category').value = mediaData.category;
                    document.getElementById('accession_number').value = mediaData.accession_number;
                    
                    // Update modal title
                    document.getElementById('mediaModalLabel').textContent = 'Edit Media Resource';
                });
            });

            // Reset form when modal is closed
            const mediaModal = document.getElementById('mediaModal');
            mediaModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('mediaForm').reset();
                document.getElementById('resourceId').value = '';
                document.getElementById('mediaModalLabel').textContent = 'Add New Media Resource';
            });
        });

        function printMedia(media) {
            // Create print window content
            const printContent = `
                <html>
                <head>
                    <title>Media Resource Details - ${media.title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .media-details { max-width: 800px; margin: 20px auto; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .detail-row { margin-bottom: 15px; }
                        .label { font-weight: bold; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="media-details">
                        <div class="header">
                            <h2>Media Resource Details</h2>
                            <p>Generated on ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="detail-row">
                            <span class="label">Title:</span> ${media.title}
                        </div>
                        <div class="detail-row">
                            <span class="label">Format:</span> ${media.format}
                        </div>
                        <div class="detail-row">
                            <span class="label">Runtime:</span> ${media.runtime} min
                        </div>
                        <div class="detail-row">
                            <span class="label">Media Type:</span> ${media.media_type}
                        </div>
                        <div class="detail-row">
                            <span class="label">Accession Number:</span> ${media.accession_number}
                        </div>
                        <div class="detail-row">
                            <span class="label">Category:</span> ${media.category}
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