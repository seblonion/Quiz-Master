document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if we're on the profile page
    const difficultyData = document.getElementById('difficultyData');
    const monthlyData = document.getElementById('monthlyData');
    
    if (difficultyData && monthlyData) {
        initCharts();
    }
    
    function initCharts() {
        // Difficulty distribution chart
        const difficultyChart = document.getElementById('difficultyChart');
        if (difficultyChart) {
            const difficulties = JSON.parse(difficultyData.getAttribute('data-difficulties') || '[]');
            const counts = JSON.parse(difficultyData.getAttribute('data-counts') || '[]');
            
            if (difficulties.length > 0 && counts.length > 0) {
                new Chart(difficultyChart, {
                    type: 'doughnut',
                    data: {
                        labels: difficulties,
                        datasets: [{
                            data: counts,
                            backgroundColor: [
                                '#22c55e', // Facile - vert
                                '#eab308', // Moyen - jaune
                                '#ef4444'  // Difficile - rouge
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} quiz (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                difficultyChart.parentElement.innerHTML = '<p class="no-data">Aucune donnée disponible</p>';
            }
        }
        
        // Monthly progress chart
        const monthlyChart = document.getElementById('monthlyChart');
        if (monthlyChart) {
            const months = JSON.parse(monthlyData.getAttribute('data-months') || '[]');
            const scores = JSON.parse(monthlyData.getAttribute('data-scores') || '[]');
            
            if (months.length > 0 && scores.length > 0) {
                new Chart(monthlyChart, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Score moyen (%)',
                            data: scores,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            } else {
                monthlyChart.parentElement.innerHTML = '<p class="no-data">Aucune donnée disponible</p>';
            }
        }
    }
});