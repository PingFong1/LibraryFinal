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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize chart data that PHP provides
    const initialResourceData = {
        books: <?php echo $resourceController->getTotalBooks(); ?>,
        mediaResources: <?php echo $resourceController->getTotalMediaResources(); ?>,
        periodicals: <?php echo $resourceController->getTotalPeriodicals(); ?>
    };
    const initialMonthlyData = [<?php echo implode(',', $monthlyBorrowings); ?>];
</script>
<?php endif; ?>
