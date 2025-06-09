<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

// Start the session and check login status
Session::start();

// Restrict access to admin and staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Fetch all borrowings
$borrowings = $borrowingController->getAllBorrowings();

try {
    $conn = (new Database())->getConnection();
    $query = "SELECT 
                b.borrowing_id, 
                u.first_name, 
                u.last_name, 
                u.email, 
                u.role,
                lr.title AS resource_title,
                lr.category AS resource_type,
                b.borrow_date, 
                b.due_date, 
                b.status,
                DATEDIFF(CURRENT_DATE, b.due_date) as days_overdue,
                fc.fine_amount as daily_fine_rate,
                CASE 
                    WHEN DATEDIFF(CURRENT_DATE, b.due_date) > 0 
                    THEN DATEDIFF(CURRENT_DATE, b.due_date) * fc.fine_amount 
                    ELSE 0 
                END as calculated_fine,
                (SELECT COUNT(*) FROM fine_payments fp 
                 WHERE fp.borrowing_id = b.borrowing_id 
                 AND fp.payment_status = 'paid') as payment_count,
                (SELECT COALESCE(SUM(amount_paid), 0) FROM fine_payments fp 
                 WHERE fp.borrowing_id = b.borrowing_id 
                 AND fp.payment_status = 'paid') as total_paid
              FROM borrowings b
              JOIN users u ON b.user_id = u.user_id
              JOIN library_resources lr ON b.resource_id = lr.resource_id
              JOIN fine_configurations fc ON lr.category = fc.resource_type
              WHERE (b.status = 'overdue' OR 
                    (b.status = 'active' AND b.due_date < CURRENT_DATE))
              AND (
                SELECT COALESCE(SUM(amount_paid), 0) 
                FROM fine_payments fp 
                WHERE fp.borrowing_id = b.borrowing_id 
                AND fp.payment_status = 'paid'
              ) < (
                CASE 
                    WHEN DATEDIFF(CURRENT_DATE, b.due_date) > 0 
                    THEN DATEDIFF(CURRENT_DATE, b.due_date) * fc.fine_amount 
                    ELSE 0 
                END
              )
              ORDER BY b.due_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $overdueResources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total fines
    $totalOverdueFines = array_reduce($overdueResources, function($carry, $borrowing) {
        return $carry + $borrowing['calculated_fine'];
    }, 0);
} catch (PDOException $e) {
    error_log("Overdue management error: " . $e->getMessage());
    $overdueResources = [];
    $totalOverdueFines = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Management | Library Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
    <style>
        body {
            background-color: #f4f6f9;
        }
        .overdue-management-container {
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
        .overdue-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="overdue-management-container">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-calendar2-x me-2"></i>Overdue Management
                    </h2>
                    <div class="box p-3 border rounded">
                            <span class="me-3">Total Overdue: <?php echo count($overdueResources); ?></span>
                            <span>Total Overdue Fines: $<?php echo number_format($totalOverdueFines, 2); ?></span>
                    </div>
                </div>
                
                <?php if (empty($overdueResources)): ?>
                    <div class="alert alert-info">No overdue resources at the moment.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Borrower</th>
                                    <th>Resource</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Fine Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueResources as $borrowing): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['role']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($borrowing['resource_title']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['resource_type']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></td>
                                        <td>
                                        <span class="text-danger fw-bold">
                                                <?php echo $borrowing['days_overdue']; ?> days
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">$<?php echo number_format($borrowing['calculated_fine'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">Rate: $<?php echo number_format($borrowing['daily_fine_rate'], 2); ?>/day</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" 
                                                    data-bs-target="#overdueModal<?php echo $borrowing['borrowing_id']; ?>">
                                                <i class="bi bi-exclamation-triangle"></i> Details
                                            </button>
                                            <button class="btn btn-sm btn-success" onclick="openPayFineModal(<?php echo $borrowing['borrowing_id']; ?>, <?php echo $borrowing['calculated_fine']; ?>)">
                                                <i class="bi bi-cash"></i> Pay Fine
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Overdue Details Modal -->
                                    <div class="modal fade" id="overdueModal<?php echo $borrowing['borrowing_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Overdue Resource Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h6>Borrower Information</h6>
                                                    <p>
                                                        <strong>Name:</strong> <?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?><br>
                                                        <strong>Email:</strong> <?php echo htmlspecialchars($borrowing['email']); ?><br>
                                                        <strong>Role:</strong> <?php echo htmlspecialchars($borrowing['role']); ?>
                                                    </p>

                                                    <h6>Resource Details</h6>
                                                    <p>
                                                        <strong>Title:</strong> <?php echo htmlspecialchars($borrowing['resource_title']); ?><br>
                                                        <strong>Type:</strong> <?php echo htmlspecialchars($borrowing['resource_type']); ?>
                                                    </p>

                                                    <h6>Overdue Information</h6>
                                                    <p>
                                                        <strong>Borrow Date:</strong> <?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?><br>
                                                        <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?><br>
                                                        <strong>Days Overdue:</strong> <?php echo $borrowing['days_overdue']; ?> days<br>
                                                        <strong>Daily Fine Rate:</strong> $<?php echo number_format($borrowing['daily_fine_rate'], 2); ?><br>
                                                        <strong>Total Fine Amount:</strong> $<?php echo number_format($borrowing['calculated_fine'], 2); ?><br>
                                                        <strong>Amount Paid:</strong> $<?php echo number_format($borrowing['total_paid'], 2); ?><br>
                                                        <strong>Remaining Balance:</strong> $<?php echo number_format($borrowing['calculated_fine'] - $borrowing['total_paid'], 2); ?>
                                                    </p>

                                                    <?php
                                                    // Fetch payment history
                                                    $paymentQuery = "SELECT 
                                                        fp.payment_id,
                                                        fp.amount_paid,
                                                        fp.payment_date,
                                                        fp.payment_status,
                                                        fp.payment_notes,
                                                        CONCAT(u.first_name, ' ', u.last_name) as processed_by_name
                                                    FROM fine_payments fp
                                                    LEFT JOIN users u ON fp.processed_by = u.user_id
                                                    WHERE fp.borrowing_id = :borrowing_id
                                                    ORDER BY fp.payment_date DESC";
                                                    
                                                    $paymentStmt = $conn->prepare($paymentQuery);
                                                    $paymentStmt->execute([':borrowing_id' => $borrowing['borrowing_id']]);
                                                    $payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    if (!empty($payments)): ?>
                                                        <h6 class="mt-3">Payment History</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date</th>
                                                                        <th>Amount</th>
                                                                        <th>Status</th>
                                                                        <th>Processed By</th>
                                                                        <th>Notes</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($payments as $payment): ?>
                                                                        <tr>
                                                                            <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                                                            <td>$<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                                                            <td>
                                                                                <span class="badge <?php echo $payment['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                                                                    <?php echo ucfirst($payment['payment_status']); ?>
                                                                                </span>
                                                                            </td>
                                                                            <td><?php echo htmlspecialchars($payment['processed_by_name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($payment['payment_notes'] ?? ''); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary">Contact Borrower</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Pay Fine Modal -->
    <div class="modal fade" id="payFineModal" tabindex="-1" aria-labelledby="payFineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payFineModalLabel">Pay Fine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="payFineForm" action="process_fine_payment.php" method="POST">
                     <input type="hidden" name="borrowing_id" id="modal_borrowing_id">
                    <input type="hidden" name="calculated_fine" id="modal_calculated_fine">
                    
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Fine Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cash_received" class="form-label">Cash Received</label>
                        <input type="number" step="0.01" class="form-control" id="cash_received" name="cash_received" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="change_amount" class="form-label">Change</label>
                        <input type="number" step="0.01" class="form-control" id="change_amount" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Payment Notes</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitPayment" disabled>Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
 // Ensure this is added before other scripts
document.addEventListener('DOMContentLoaded', function() {
    const cashReceivedInput = document.getElementById('cash_received');
    const fineAmountInput = document.getElementById('amount_paid');
    const changeAmountInput = document.getElementById('change_amount');
    const submitPaymentButton = document.getElementById('submitPayment');
    const payFineForm = document.getElementById('payFineForm');

    // Input validation and change calculation
    cashReceivedInput.addEventListener('input', function() {
        const fineAmount = parseFloat(fineAmountInput.value) || 0;
        const cashReceived = parseFloat(this.value) || 0;
        
        // Calculate change
        const change = cashReceived - fineAmount;
        changeAmountInput.value = change.toFixed(2);
        
        // Enable/disable submit button based on payment amount
        submitPaymentButton.disabled = cashReceived < fineAmount;
    });

    // Modal opening function - now accepts borrowing ID and fine amount
    window.openPayFineModal = function(borrowingId, calculatedFine) {
        document.getElementById('modal_borrowing_id').value = borrowingId;
        document.getElementById('modal_calculated_fine').value = calculatedFine;
        fineAmountInput.value = calculatedFine;
        cashReceivedInput.value = '';
        changeAmountInput.value = '';
        document.getElementById('payment_notes').value = '';
        submitPaymentButton.disabled = true;
        
        var modal = new bootstrap.Modal(document.getElementById('payFineModal'));
        modal.show();
    };

    // Form submission handler
    payFineForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        const fineAmount = parseFloat(fineAmountInput.value);
        const cashReceived = parseFloat(cashReceivedInput.value);

        // Additional validation
        if (cashReceived < fineAmount) {
            alert('Cash received must be greater than or equal to the fine amount.');
            return;
        }

        // Print receipt
        printReceipt();

        // Here you might want to submit the form via AJAX or allow standard form submission
        this.submit();
    });

    function printReceipt() {
        const fineAmount = document.getElementById('amount_paid').value;
        const cashReceived = document.getElementById('cash_received').value;
        const change = document.getElementById('change_amount').value;
        const paymentNotes = document.getElementById('payment_notes').value;

        const receiptHTML = `
            <style>
                /* ... (previous receipt styles remain the same) ... */
            </style>

            <div class="receipt-header">
                <h2>Payment Receipt</h2>
                <p>Date: ${new Date().toLocaleDateString()}</p>
                <p>Time: ${new Date().toLocaleTimeString()}</p>
            </div>

            <div class="receipt-body">
                <table>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                    <tr>
                        <td>Fine Payment</td>
                        <td>$${fineAmount}</td>
                    </tr>
                    <tr>
                        <td>Cash Received</td>
                        <td>$${cashReceived}</td>
                    </tr>
                    <tr>
                        <td>Change</td>
                        <td>$${change}</td>
                    </tr>
                </table>
                <p>Payment Notes: ${paymentNotes}</p>
            </div>

            <div class="receipt-footer">
                <p>Thank you for your payment!</p>
            </div>
        `;

        const printWindow = window.open('', 'print');
        printWindow.document.write(receiptHTML);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
});
</script>
</body>
</html>






