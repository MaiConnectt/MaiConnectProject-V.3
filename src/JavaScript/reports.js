/**
 * ===================================================================
 * Archivo: reports.js
 * Propósito: Instancia y dibuja los gráficos (Chart.js) pasados desde
 *            el backend hacia la vista (con var global ReportsData).
 * ===================================================================
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || typeof window.ReportsData === 'undefined') return;

    // Configuración común
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#6e5c5f';

    const data = window.ReportsData;

    // 2. Estado de Pedidos
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && data.status) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: data.status.labels,
                datasets: [{
                    data: data.status.data,
                    backgroundColor: data.status.colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%', // Dona más delgada y elegante
                plugins: { 
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } 
                }
            }
        });
    }

    // 3. Top Productos (Barras Horizontales)
    const productsCtx = document.getElementById('productsChart');
    if (productsCtx && data.products) {
        const ctx = productsCtx.getContext('2d');
        const prodGradient = ctx.createLinearGradient(0, 0, 400, 0);
        prodGradient.addColorStop(0, '#e8bdc4');
        prodGradient.addColorStop(1, '#c97c89');

        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: data.products.map(p => p.name),
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: data.products.map(p => p.total_sold),
                    backgroundColor: prodGradient,
                    borderRadius: 8,
                    maxBarThickness: 45,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: { grid: { borderDash: [4, 4] } },
                    y: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    // 4. Ventas por Mes (Línea)
    const salesMonthCtx = document.getElementById('salesMonthChart');
    if (salesMonthCtx && data.salesMonth) {
        const ctx = salesMonthCtx.getContext('2d');
        const salesGradient = ctx.createLinearGradient(0, 0, 0, 400);
        salesGradient.addColorStop(0, 'rgba(166, 92, 104, 0.4)');
        salesGradient.addColorStop(1, 'rgba(166, 92, 104, 0.05)');

        new Chart(salesMonthCtx, {
            type: 'line',
            data: {
                labels: data.salesMonth.map(r => r.mes_label),
                datasets: [{
                    label: 'Ventas',
                    data: data.salesMonth.map(r => parseFloat(r.total_ventas)),
                    borderColor: '#a65c68',
                    backgroundColor: salesGradient,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#a65c68',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4] },
                        ticks: { callback: val => '$' + val.toLocaleString('es-CO') }
                    }
                }
            }
        });
    }

    // 5. Vendedores por Universidad (Barras)
    const uniCtx = document.getElementById('uniChart');
    if (uniCtx && data.universities) {
        const ctx = uniCtx.getContext('2d');
        const uniGradient = ctx.createLinearGradient(0, 0, 0, 300);
        uniGradient.addColorStop(0, 'rgba(201, 124, 137, 0.8)');
        uniGradient.addColorStop(1, 'rgba(201, 124, 137, 0.2)');

        new Chart(uniCtx, {
            type: 'bar',
            data: {
                labels: data.universities.map(r => r.universidad),
                datasets: [{
                    label: 'Vendedores',
                    data: data.universities.map(r => parseInt(r.total_vendedores)),
                    backgroundColor: uniGradient,
                    borderColor: '#c97c89',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    maxBarThickness: 60,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { borderDash: [4, 4] } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
});
