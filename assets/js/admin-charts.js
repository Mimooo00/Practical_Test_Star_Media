(function () {
    'use strict';

    var dataElement = document.getElementById('admin-chart-data');

    if (!dataElement || typeof Chart === 'undefined') {
        return;
    }

    var data;

    try {
        data = JSON.parse(dataElement.textContent || '{}');
    } catch (error) {
        return;
    }

    Chart.defaults.font.family = 'Inter, "Segoe UI", system-ui, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';

    var lineCanvas = document.getElementById('chart-over-time');

    if (lineCanvas && data.line && data.line.labels && data.line.labels.length) {
        new Chart(lineCanvas, {
            type: 'line',
            data: {
                labels: data.line.labels,
                datasets: data.line.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 16
                        }
                    },
                    tooltip: {
                        padding: 10,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)'
                        }
                    }
                }
            }
        });
    }

    var platformCanvas = document.getElementById('chart-platforms');

    if (platformCanvas && data.doughnut && data.doughnut.labels && data.doughnut.labels.length) {
        new Chart(platformCanvas, {
            type: 'doughnut',
            data: {
                labels: data.doughnut.labels,
                datasets: [{
                    data: data.doughnut.data,
                    backgroundColor: data.doughnut.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14
                        }
                    },
                    tooltip: {
                        padding: 10
                    }
                }
            }
        });
    }

    var topPagesCanvas = document.getElementById('chart-top-pages');

    if (topPagesCanvas && data.topPages && data.topPages.labels && data.topPages.labels.length) {
        new Chart(topPagesCanvas, {
            type: 'bar',
            data: {
                labels: data.topPages.labels,
                datasets: [{
                    label: 'Share clicks',
                    data: data.topPages.data,
                    backgroundColor: 'rgba(41, 85, 76, 0.78)',
                    hoverBackgroundColor: 'rgba(31, 57, 52, 1)',
                    borderRadius: 6,
                    maxBarThickness: 34
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        padding: 10
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
})();
