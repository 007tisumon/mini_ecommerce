$(function() {
    var data = window.dashboardData || {};
    var orders = document.getElementById('chart1');
    var payments = document.getElementById('chart2');

    if (orders && window.Chart) {
        new Chart(orders, {
            type: 'line',
            data: {
                labels: data.months || [],
                datasets: [{
                    label: 'Orders',
                    data: data.orders || [],
                    borderColor: '#fff',
                    backgroundColor: 'rgb(255 255 255 / 20%)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    if (payments && window.Chart) {
        new Chart(payments, {
            type: 'doughnut',
            data: {
                labels: data.paymentLabels || [],
                datasets: [{
                    data: data.paymentCounts || [],
                    backgroundColor: ['#fff', 'rgb(255 255 255 / 60%)', 'rgb(255 255 255 / 35%)', 'rgb(255 255 255 / 15%)'],
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
