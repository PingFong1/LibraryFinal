document.addEventListener('DOMContentLoaded', function() {
    // Resource Distribution Chart
    function initializeResourceChart() {
        var ctx = document.getElementById('resourceDistributionChart').getContext('2d');
        var resourceData = {
            labels: ['Books', 'Periodicals', 'Media Resources'],
            datasets: [{
                data: [
                    initialResourceData.books,
                    initialResourceData.periodicals,
                    initialResourceData.mediaResources
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
    }

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

    // Initialize charts if elements exist
    if (document.getElementById('resourceDistributionChart')) {
        initializeResourceChart();
    }
    
    if (document.getElementById('monthlyBorrowingsChart')) {
        initializeMonthlyChart(initialMonthlyData);
    }

    // Year selector event listener
    const yearSelector = document.getElementById('yearSelector');
    if (yearSelector) {
        yearSelector.addEventListener('change', function() {
            fetch(`../controller/get_monthly_borrowings.php?year=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    initializeMonthlyChart(data);
                })
                .catch(error => console.error('Error:', error));
        });
    }
});
