<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';
require_once '../controller/ResourceController.php';
require_once '../controller/BookController.php';
require_once '../controller/PeriodicalController.php';
require_once '../controller/MediaResourceController.php';
require_once '../controller/UserController.php';
require_once '../vendor/autoload.php'; // Make sure TCPDF is installed via composer

Session::start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$borrowingController = new BorrowingController();
$resourceController = new ResourceController();
$bookController = new BookController();
$periodicalController = new PeriodicalController();
$mediaResourceController = new MediaResourceController();
$userController = new UserController();

// Get statistics and data
$borrowings = $borrowingController->getAllBorrowings();
$resourceStats = $resourceController->getBookStatistics();
$categoryDistribution = $resourceController->getBookCategoriesDistribution();
$overdueBorrowings = $borrowingController->getOverdueBorrowings();

// Get resource type totals
$totalBooks = $bookController->getTotalBooks();
$totalPeriodicals = $periodicalController->getTotalPeriodicals();
$totalMediaResources = $mediaResourceController->getTotalMediaResources();

// Get user statistics
$userStats = $userController->getUserStatistics();

// Modify the generatePDF function to exclude current borrowings in full report
function generatePDF($borrowings, $resourceStats, $totalBooks, $totalPeriodicals, $totalMediaResources, $userStats, $reportType = 'full', $overdueBorrowings = []) {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Library Management System');
    $pdf->SetAuthor('System Administrator');
    $pdf->SetTitle(match($reportType) {
        'full' => 'Library Resources Report',
        'borrowings' => 'Current Borrowings Report',
        'overdue' => 'Overdue Books and Fines Report',
        default => 'Library Report'
    });

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Library Management System', 'Generated Report - ' . date('Y-m-d H:i:s'));

    // Set header and footer fonts
    $pdf->setHeaderFont(Array('helvetica', '', 10));
    $pdf->setFooterFont(Array('helvetica', '', 8));

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', 'B', 16);

    // Title
    $pdf->Cell(0, 15, match($reportType) {
        'full' => 'Library Resources Report',
        'borrowings' => 'Current Borrowings Report',
        'overdue' => 'Overdue Books and Fines Report',
        default => 'Library Report'
    }, 0, 1, 'C');
    $pdf->Ln(10);

    if ($reportType === 'full') {
        // Resource Statistics
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Resource Statistics', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        
        $pdf->Cell(100, 8, 'Total Resources:', 0, 0);
        $pdf->Cell(0, 8, $resourceStats['total_books'], 0, 1);
        
        $pdf->Cell(100, 8, 'Available Resources:', 0, 0);
        $pdf->Cell(0, 8, $resourceStats['available_books'], 0, 1);
        
        $pdf->Cell(100, 8, 'Borrowed Resources:', 0, 0);
        $pdf->Cell(0, 8, $resourceStats['borrowed_books'], 0, 1);
        
        $pdf->Cell(100, 8, 'Overdue Resources:', 0, 0);
        $pdf->Cell(0, 8, $resourceStats['overdue_books'], 0, 1);
        
        $pdf->Ln(10);

        // Resource Type Distribution
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Resource Type Distribution', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        
        $pdf->Cell(100, 8, 'Books:', 0, 0);
        $pdf->Cell(0, 8, $totalBooks, 0, 1);
        
        $pdf->Cell(100, 8, 'Periodicals:', 0, 0);
        $pdf->Cell(0, 8, $totalPeriodicals, 0, 1);
        
        $pdf->Cell(100, 8, 'Media Resources:', 0, 0);
        $pdf->Cell(0, 8, $totalMediaResources, 0, 1);
        
        $pdf->Ln(10);

        // User Statistics
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'User Statistics', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        
        $pdf->Cell(100, 8, 'Total Users:', 0, 0);
        $pdf->Cell(0, 8, $userStats['total_users'], 0, 1);
        
        $pdf->Cell(100, 8, 'Students:', 0, 0);
        $pdf->Cell(0, 8, $userStats['student_count'], 0, 1);
        
        $pdf->Cell(100, 8, 'Faculty Members:', 0, 0);
        $pdf->Cell(0, 8, $userStats['faculty_count'], 0, 1);
        
        $pdf->Cell(100, 8, 'Staff Members:', 0, 0);
        $pdf->Cell(0, 8, $userStats['staff_count'], 0, 1);
        
        $pdf->Cell(100, 8, 'Administrators:', 0, 0);
        $pdf->Cell(0, 8, $userStats['admin_count'], 0, 1);
        
        $pdf->Ln(10);

        // Add Overdue Books Section
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Overdue Books and Fines', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        // Table header
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(45, 8, 'User', 1, 0, 'C', true);
        $pdf->Cell(55, 8, 'Resource', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Due Date', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Days Overdue', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Fine Amount', 1, 1, 'C', true);

        // Table content
        foreach ($overdueBorrowings as $borrowing) {
            $pdf->Cell(45, 8, $borrowing['first_name'] . ' ' . $borrowing['last_name'], 1, 0, 'L');
            $pdf->Cell(55, 8, $borrowing['resource_title'], 1, 0, 'L');
            $pdf->Cell(30, 8, date('Y-m-d', strtotime($borrowing['due_date'])), 1, 0, 'C');
            $pdf->Cell(30, 8, $borrowing['days_overdue'], 1, 0, 'C');
            $pdf->Cell(30, 8, '$' . number_format($borrowing['fine_amount'], 2), 1, 1, 'C');
            
        }

        // Add total fines
        $totalFines = array_sum(array_column($overdueBorrowings, 'fine_amount'));
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(160, 8, 'Total Outstanding Fines:', 1, 0, 'R');
        $pdf->Cell(30, 8, '$' . number_format($totalFines, 2), 1, 1, 'C');
    } elseif ($reportType === 'borrowings') {
        // Current Borrowings
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Current Borrowings', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        // Table header
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(50, 8, 'User', 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Resource', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Borrow Date', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Due Date', 1, 1, 'C', true);

        // Table content
        foreach ($borrowings as $borrowing) {
            $pdf->Cell(50, 8, $borrowing['first_name'] . ' ' . $borrowing['last_name'], 1, 0, 'L');
            $pdf->Cell(60, 8, $borrowing['resource_title'], 1, 0, 'L');
            $pdf->Cell(40, 8, date('Y-m-d', strtotime($borrowing['borrow_date'])), 1, 0, 'C');
            $pdf->Cell(40, 8, date('Y-m-d', strtotime($borrowing['due_date'])), 1, 1, 'C');
        }
    } elseif ($reportType === 'overdue') {
        // Overdue Books Section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Overdue Books and Fines', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        // Table header
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(45, 8, 'User', 1, 0, 'C', true);
        $pdf->Cell(55, 8, 'Resource', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Due Date', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Days Overdue', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Fine Amount', 1, 1, 'C', true);

        // Table content
        foreach ($overdueBorrowings as $borrowing) {
            $pdf->Cell(45, 8, $borrowing['first_name'] . ' ' . $borrowing['last_name'], 1, 0, 'L');
            $pdf->Cell(55, 8, $borrowing['resource_title'], 1, 0, 'L');
            $pdf->Cell(30, 8, date('Y-m-d', strtotime($borrowing['due_date'])), 1, 0, 'C');
            $pdf->Cell(30, 8, $borrowing['days_overdue'], 1, 0, 'C');
            $pdf->Cell(30, 8, '$' . number_format($borrowing['fine_amount'], 2), 1, 1, 'C');
        }

        // Add total fines
        $totalFines = array_sum(array_column($overdueBorrowings, 'fine_amount'));
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(160, 8, 'Total Outstanding Fines:', 1, 0, 'R');
        $pdf->Cell(30, 8, '$' . number_format($totalFines, 2), 1, 1, 'C');

        // Add summary
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Summary:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 8, 'Total Overdue Items: ' . count($overdueBorrowings), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Total Outstanding Fines: $' . number_format($totalFines, 2), 0, 1, 'L');
    }

    return $pdf;
}

// Handle PDF generation request
if (isset($_POST['generate_pdf'])) {
    $reportType = $_POST['report_type'] ?? 'full';
    $pdf = generatePDF($borrowings, $resourceStats, $totalBooks, $totalPeriodicals, $totalMediaResources, $userStats, $reportType, $overdueBorrowings);
    $pdf->Output('library_report_' . date('Y-m-d') . '.pdf', 'D');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <div class="flex-grow-1 content">
            <div class="container-fluid mt-4">
                <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
                    <div class="col-md-6">
                        <h2 class="text-center mb-4">Report Generation</h2>

                        <div class="card shadow">
                            <div class="card-body">
                                <p class="text-center mb-4">Select the type of report you want to generate:</p>
                                <form method="post" class="text-center">
                                    <div class="mb-4">
                                        <div class="form-check mb-2 d-inline-block text-start">
                                            <input class="form-check-input" type="radio" name="report_type" id="fullReport" value="full" checked>
                                            <label class="form-check-label" for="fullReport">
                                                Library Resources Report
                                            </label>
                                        </div>
                                        <div class="form-check mb-2 d-inline-block text-start ms-4">
                                            <input class="form-check-input" type="radio" name="report_type" id="borrowingsReport" value="borrowings">
                                            <label class="form-check-label" for="borrowingsReport">
                                                Current Borrowings
                                            </label>
                                        </div>
                                        <div class="form-check mb-3 d-inline-block text-start ms-4">
                                            <input class="form-check-input" type="radio" name="report_type" id="overdueReport" value="overdue">
                                            <label class="form-check-label" for="overdueReport">
                                                Overdue Books & Fines
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" name="generate_pdf" class="btn btn-primary px-4">
                                        <i class="bi bi-file-pdf me-2"></i>Generate PDF Report
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>