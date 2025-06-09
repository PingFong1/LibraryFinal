<?php
require_once '../config/Database.php';
require_once '../controller/ResourceController.php';
require_once '../controller/BookController.php';
require_once '../controller/MediaResourceController.php';
require_once '../controller/PeriodicalController.php';
require_once '../controller/BorrowingController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch book statistics
$resourceController = new ResourceController();
$bookStats = $resourceController->getBookStatistics();
$categoryDistribution = $resourceController->getBookCategoriesDistribution();
$monthlyBorrowings = $resourceController->getMonthlyBorrowings(date('Y'));
$popularResources = $resourceController->getMostBorrowedResources();
?>

<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebarModal.php'; ?>

        <div class="main-content flex-grow-1">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center py-3 px-4">
            <h2 class="fw-bold">Welcome, <small class="fw-normal" style="font-size: 0.8em;"><?php echo htmlspecialchars($_SESSION['username']); ?></small></h2>
                <!-- User Profile Section -->
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 dropdown-toggle" 
                         role="button" data-bs-toggle="dropdown" aria-expanded="false">
                       
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                            <span class="badge text-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" style="background-color: rgb(184, 207, 202);">
                        <li class="fas fa-people">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountSettingsModal">Account Settings</a>
                        </li>
                    </ul>
                </div>
            </div>
            <hr>

            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #2B3377;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-book-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Total Books</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['total_books']; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #0047FF;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-journal-check text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Available Books</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['available_books']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #FF00FF;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-journal-arrow-up text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Borrowed Books</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['borrowed_books']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #FF6600;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Overdue Books</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['overdue_books']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Replace both Top Choices and Most Borrowed Resources sections with this include -->
            <?php include 'includes/most_borrowed_resources.php'; ?>

            <!-- Chart Section (Previously in chart.php) -->
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0 fw-bold">Borrowing Trends</h5>
                                <select id="yearSelector" class="form-select form-select-sm" style="width: auto;">
                                    <?php
                                    $currentYear = date('Y');
                                    for($year = $currentYear; $year >= $currentYear - 4; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="monthlyBorrowingsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 fw-bold">Resource Distribution</h5>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="resourceDistributionChart"></canvas>
                            </div>l
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize chart data that PHP provides
        const initialResourceData = {
            books: <?php echo $resourceController->getTotalBooks(); ?>,
            mediaResources: <?php echo $resourceController->getTotalMediaResources(); ?>,
            periodicals: <?php echo $resourceController->getTotalPeriodicals(); ?>
        };
        const initialMonthlyData = [<?php echo implode(',', $monthlyBorrowings); ?>];
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Resource Distribution Chart
            var ctx = document.getElementById('resourceDistributionChart').getContext('2d');
            var resourceData = {
                labels: ['Books', 'Periodicals', 'Media Resources'],
                datasets: [{
                    data: [
                        <?php echo $resourceController->getTotalBooks(); ?>,
                        <?php echo $resourceController->getTotalPeriodicals(); ?>,
                        <?php echo $resourceController->getTotalMediaResources(); ?>
                    ],
                    backgroundColor: [
                        '#2B3377',  // Dark blue for Books
                        '#FF00FF',  // Magenta for Periodicals
                        '#0047FF'   // Bright blue for Media Resources
                    ],
                    borderWidth: 0,
                    spacing: 2,
                    hoverOffset: 15
                }]
            };
            
            new Chart(ctx, {
                type: 'pie',
                data: resourceData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: 'bold',
                                    family: "'Arial', sans-serif"
                                },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    return data.labels.map((label, i) => ({
                                        text: `${label} (${data.datasets[0].data[i]})`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        index: i
                                    }));
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.raw} items`;
                                }
                            },
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#000',
                            bodyColor: '#000',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true,
                            bodyFont: {
                                size: 12
                            }
                        }
                    },
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 20,
                            left: 20,
                            right: 20
                        }
                    }
                }
            });

            // Monthly Borrowings Chart
            let monthlyBorrowingsChart;

            function initializeMonthlyChart(data) {
                var monthlyCtx = document.getElementById('monthlyBorrowingsChart').getContext('2d');
                var monthlyData = {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Borrowings',
                        data: data,
                        backgroundColor: 'rgba(43, 51, 119, 0.8)',
                        borderColor: '#2B3377',
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 40,
                        hoverBackgroundColor: '#2B3377'
                    }]
                };
                
                if (monthlyBorrowingsChart) {
                    monthlyBorrowingsChart.destroy();
                }
                
                monthlyBorrowingsChart = new Chart(monthlyCtx, {
                    type: 'bar',
                    data: monthlyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 2,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 12,
                                        family: "'Arial', sans-serif"
                                    },
                                    padding: 10
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12,
                                        family: "'Arial', sans-serif"
                                    },
                                    padding: 5
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Monthly Borrowings ' + document.getElementById('yearSelector').value,
                                font: {
                                    size: 16,
                                    weight: 'bold',
                                    family: "'Arial', sans-serif"
                                },
                                padding: {
                                    top: 10,
                                    bottom: 30
                                },
                                color: '#2B3377'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#000',
                                bodyColor: '#000',
                                borderColor: '#ddd',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.parsed.y} borrowings`;
                                    }
                                }
                            }
                        },
                        hover: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                });
            }

            initializeMonthlyChart(initialMonthlyData);

            document.getElementById('yearSelector').addEventListener('change', function() {
                fetch(`../controller/get_monthly_borrowings.php?year=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        initializeMonthlyChart(data);
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>

    <?php include 'includes/accountSettingsModal.php'; ?>

    <!-- Resource Details Modal -->
    <div class="modal fade" id="resourceDetailsModal" tabindex="-1" aria-labelledby="resourceDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resourceDetailsModalLabel">Resource Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resourceDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modify the image sections to be clickable -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make all resource images clickable
        const resourceImages = document.querySelectorAll('.resource-image');
        resourceImages.forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                const resourceId = this.dataset.resourceId;
                const resourceType = this.dataset.resourceType;
                fetchResourceDetails(resourceId, resourceType);
            });
        });

        function fetchResourceDetails(resourceId, resourceType) {
            fetch(`../api/get_resource_details.php?resource_id=${resourceId}&type=${resourceType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResourceDetails(data.resource);
                    } else {
                        alert('Error loading resource details');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function displayResourceDetails(resource) {
            // Get status color based on the status value
            const statusColor = resource.status.toLowerCase() === 'available' 
                ? 'text-success' 
                : resource.status.toLowerCase() === 'borrowed' 
                    ? 'text-warning' 
                    : 'text-muted';

            let detailsHtml = `
                <div class="text-center mb-4">
                    <img src="../${resource.cover_image || 'assets/images/default1.png'}" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-height: 200px;" 
                         alt="Resource Cover">
                </div>
                <div class="resource-details">
                    <h6 class="fw-bold">Title:</h6>
                    <p>${resource.title}</p>
                    <h6 class="fw-bold">Category:</h6>
                    <p>${resource.category}</p>
                    <h6 class="fw-bold">Status:</h6>
                    <p class="fw-bold ${statusColor}">${resource.status.toUpperCase()}</p>`;

            // // Add specific details based on resource type
            // if (resource.author) {
            //     detailsHtml += `
            //         <h6 class="fw-bold">Author:</h6>
            //         <p>${resource.author}</p>
            //         <h6 class="fw-bold">ISBN:</h6>
            //         <p>${resource.isbn}</p>
            //         <h6 class="fw-bold">Publisher:</h6>
            //         <p>${resource.publisher}</p>
            //         <h6 class="fw-bold">Edition:</h6>
            //         <p>${resource.edition}</p>
            //         <h6 class="fw-bold">Publication Date:</h6>
            //         <p>${resource.publication_date}</p>`;
            // }

            document.getElementById('resourceDetailsContent').innerHTML = detailsHtml;
            new bootstrap.Modal(document.getElementById('resourceDetailsModal')).show();
        }
    });
    </script>
</body>
</html>