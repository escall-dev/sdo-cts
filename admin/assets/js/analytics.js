/**
 * Analytics Dashboard JS
 */

const analyticsState = {
    charts: {},
    data: null,
    view: 'filed',
    sort: 'volume',
    officeFilter: '',
    unitFilter: ''
};

function buildQueryParams(form) {
    const params = new URLSearchParams();
    const formData = new FormData(form);
    for (const [key, value] of formData.entries()) {
        if (value !== '') {
            params.append(key, value);
        }
    }
    return params.toString();
}

function formatHours(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }
    return `${parseFloat(value).toFixed(1)} hrs`;
}

function formatNumber(value) {
    if (value === null || value === undefined) {
        return '-';
    }
    return new Intl.NumberFormat().format(value);
}

function getStatusLabel(status) {
    const labels = window.analyticsConfig.statusLabels || {};
    return labels[status] || status.replace('_', ' ');
}

function getTypeLabel(type) {
    const labels = window.analyticsConfig.typeLabels || {};
    return labels[type] || type;
}

function createChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        return null;
    }
    return new Chart(ctx, config);
}

function updateChart(chart, config) {
    if (!chart) {
        return;
    }
    chart.data = config.data;
    chart.options = config.options;
    chart.update();
}

function buildLineConfig(labels, data, label, color = '#1b4a9a') {
    return {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data,
                borderColor: color,
                backgroundColor: 'rgba(27, 74, 154, 0.15)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    };
}

function buildBarConfig(labels, data, label, color = '#1b4a9a') {
    return {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label,
                data,
                backgroundColor: color
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    };
}

function buildDoughnutConfig(labels, data) {
    return {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: ['#1b4a9a', '#10b981', '#f59e0b', '#8b5cf6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    };
}

function updateVolumeCharts(volume) {
    const dailyLabels = volume.daily.map(row => row.period);
    const dailyTotals = volume.daily.map(row => row.total);
    const weeklyLabels = volume.weekly.map(row => `W${row.period}`);
    const weeklyTotals = volume.weekly.map(row => row.total);
    const monthlyLabels = volume.monthly.map(row => row.period);
    const monthlyTotals = volume.monthly.map(row => row.total);

    const dailyConfig = buildLineConfig(dailyLabels, dailyTotals, 'Daily Complaints');
    const weeklyConfig = buildBarConfig(weeklyLabels, weeklyTotals, 'Weekly Complaints', '#1b4a9a');
    const monthlyConfig = buildBarConfig(monthlyLabels, monthlyTotals, 'Monthly Complaints', '#1b4a9a');

    if (!analyticsState.charts.dailyVolume) {
        analyticsState.charts.dailyVolume = createChart('dailyVolumeChart', dailyConfig);
        analyticsState.charts.weeklyVolume = createChart('weeklyVolumeChart', weeklyConfig);
        analyticsState.charts.monthlyVolume = createChart('monthlyVolumeChart', monthlyConfig);
    } else {
        updateChart(analyticsState.charts.dailyVolume, dailyConfig);
        updateChart(analyticsState.charts.weeklyVolume, weeklyConfig);
        updateChart(analyticsState.charts.monthlyVolume, monthlyConfig);
    }
}

function updateStatusCharts(statusData) {
    const statusOrder = window.analyticsConfig.statusOrder || [];
    const totalsMap = {};
    statusData.totals.forEach(row => {
        totalsMap[row.status] = row.total;
    });

    const labels = statusOrder.map(status => getStatusLabel(status));
    const data = statusOrder.map(status => totalsMap[status] || 0);

    const config = buildBarConfig(labels, data, 'Status Totals', '#10b981');
    if (!analyticsState.charts.statusTotals) {
        analyticsState.charts.statusTotals = createChart('statusTotalsChart', config);
    } else {
        updateChart(analyticsState.charts.statusTotals, config);
    }

    const tbody = document.querySelector('#statusAverageTable tbody');
    tbody.innerHTML = '';
    statusData.avg_times.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${getStatusLabel(row.status)}</td>
            <td>${formatHours(row.avg_hours)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateResponseMetrics(responseData) {
    document.getElementById('avgFirstResponse').textContent = formatHours(responseData.avg_first_response_hours);
    document.getElementById('avgResolution').textContent = formatHours(responseData.avg_resolution_hours);
    document.getElementById('overdueCount').textContent = formatNumber(responseData.overdue_count);

    const tbody = document.querySelector('#overdueTable tbody');
    tbody.innerHTML = '';
    responseData.overdue_list.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.reference_number}</td>
            <td>${getStatusLabel(row.status)}</td>
            <td>${row.days_open}</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateTypeCharts(typesData) {
    const distLabels = typesData.distribution.map(row => getTypeLabel(row.type));
    const distTotals = typesData.distribution.map(row => row.total);
    const distConfig = buildDoughnutConfig(distLabels, distTotals);

    if (!analyticsState.charts.typeDistribution) {
        analyticsState.charts.typeDistribution = createChart('typeDistributionChart', distConfig);
    } else {
        updateChart(analyticsState.charts.typeDistribution, distConfig);
    }

    const periods = [...new Set(typesData.trends.map(row => row.period))];
    const datasets = Object.keys(window.analyticsConfig.typeLabels || {}).map(type => {
        const data = periods.map(period => {
            const match = typesData.trends.find(row => row.period === period && row.type === type);
            return match ? match.total : 0;
        });
        return {
            label: getTypeLabel(type),
            data,
            borderColor: type === 'facility' ? '#1b4a9a' : type === 'academic' ? '#10b981' : type === 'it' ? '#f59e0b' : '#8b5cf6',
            backgroundColor: 'transparent',
            tension: 0.3
        };
    });

    const trendConfig = {
        type: 'line',
        data: { labels: periods, datasets },
        options: { responsive: true }
    };

    if (!analyticsState.charts.typeTrend) {
        analyticsState.charts.typeTrend = createChart('typeTrendChart', trendConfig);
    } else {
        updateChart(analyticsState.charts.typeTrend, trendConfig);
    }
}

function updateUnitPerformance(unitsData) {
    const tableBody = document.querySelector('#unitPerformanceTable tbody');
    tableBody.innerHTML = '';

    const resolutionMap = {};
    unitsData.resolution.forEach(row => {
        resolutionMap[row.unit] = row;
    });
    const handlingMap = {};
    unitsData.handling_time.forEach(row => {
        handlingMap[row.unit] = row;
    });

    let rows = [];
    if (analyticsState.view === 'filed') {
        rows = unitsData.filed.map(row => ({
            unit: row.unit || '-',
            total: row.total,
            resolution_rate: resolutionMap[row.unit]?.resolution_rate ?? 0,
            avg_hours: handlingMap[row.unit]?.avg_hours ?? null
        }));
    } else {
        rows = unitsData.received.map(row => ({
            unit: row.unit || '-',
            total: row.total,
            resolution_rate: resolutionMap[row.unit]?.resolution_rate ?? 0,
            avg_hours: handlingMap[row.unit]?.avg_hours ?? null
        }));
    }

    // Apply office/unit filter from the units tab controls
    const officeUnits = window.analyticsConfig.officeUnits || {};
    const selectedOffice = analyticsState.officeFilter;
    const selectedUnit = analyticsState.unitFilter;

    if (selectedUnit) {
        // Filter to a specific unit
        rows = rows.filter(row => row.unit === selectedUnit);
    } else if (selectedOffice && officeUnits[selectedOffice]) {
        // Filter to all units belonging to the selected office
        const allowedUnits = officeUnits[selectedOffice];
        rows = rows.filter(row => allowedUnits.includes(row.unit));
    }

    const totalRowIndex = rows.findIndex(row => row.unit === '-' || row.unit === '');
    const totalRow = totalRowIndex >= 0 ? rows.splice(totalRowIndex, 1)[0] : null;

    if (analyticsState.sort === 'response') {
        rows.sort((a, b) => (a.avg_hours ?? Infinity) - (b.avg_hours ?? Infinity));
    } else if (analyticsState.sort === 'resolution') {
        rows.sort((a, b) => (b.resolution_rate ?? 0) - (a.resolution_rate ?? 0));
    } else {
        rows.sort((a, b) => (b.total ?? 0) - (a.total ?? 0));
    }

    // When filtering by office or unit, don't show a total row and show all results
    const isFiltered = selectedOffice || selectedUnit;
    const displayRows = isFiltered ? rows : rows.slice(0, totalRow ? 9 : 10);
    if (!isFiltered && totalRow) {
        displayRows.push(totalRow);
    }

    if (displayRows.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="4" style="text-align:center;color:var(--text-muted);">No data for selected filter</td>';
        tableBody.appendChild(tr);
        return;
    }

    displayRows.forEach(row => {
        const tr = document.createElement('tr');
        const unitLabel = row.unit === '-' || row.unit === '' ? 'Total' : (window.analyticsConfig.unitLabels[row.unit] || row.unit);
        tr.innerHTML = `
            <td>${unitLabel}</td>
            <td>${formatNumber(row.total)}</td>
            <td>${row.resolution_rate.toFixed(1)}%</td>
            <td>${formatHours(row.avg_hours)}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function updateLocationChart(locations) {
    const normalRows = [];
    let totalRow = null;

    locations.forEach(row => {
        const rawLocation = row.location ?? '';
        const isTotal = rawLocation === '' || rawLocation === '-';
        if (isTotal) {
            totalRow = { label: 'Total', total: row.total };
        } else {
            normalRows.push({ label: rawLocation, total: row.total });
        }
    });

    const orderedRows = totalRow ? [...normalRows, totalRow] : normalRows;
    const labels = orderedRows.map(row => row.label);
    const totals = orderedRows.map(row => row.total);
    const config = {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Complaints by Location',
                data: totals,
                backgroundColor: '#1b4a9a',
                borderRadius: 4,
                maxBarThickness: 34
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 10,
                        minRotation: 0,
                        maxRotation: 35,
                        callback: function(value) {
                            const rawLabel = this.getLabelForValue(value) || '';
                            return rawLabel.length > 18 ? `${rawLabel.slice(0, 18)}...` : rawLabel;
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    };

    if (!analyticsState.charts.location) {
        analyticsState.charts.location = createChart('locationChart', config);
    } else {
        updateChart(analyticsState.charts.location, config);
    }
}

function updateForecast(trends, volume) {
    const actualLabels = volume.monthly.map(row => row.period);
    const actualTotals = volume.monthly.map(row => row.total);
    const forecastLabels = trends.forecast.map(row => row.period);
    const forecastTotals = trends.forecast.map(row => row.total);

    if (actualTotals.length === 0) {
        return;
    }

    const labels = [...actualLabels, ...forecastLabels];
    const dataActual = [...actualTotals, ...Array(forecastLabels.length).fill(null)];
    const lastActual = actualTotals[actualTotals.length - 1];
    const dataForecast = forecastTotals.length
        ? [...Array(actualLabels.length - 1).fill(null), lastActual, ...forecastTotals]
        : [];

    const config = {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Actual',
                    data: dataActual,
                    borderColor: '#1b4a9a',
                    backgroundColor: 'rgba(27, 74, 154, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                ...(forecastTotals.length ? [{
                    label: 'Forecast',
                    data: dataForecast,
                    borderColor: '#f59e0b',
                    borderDash: [6, 4],
                    tension: 0.3,
                    fill: false
                }] : [])
            ]
        },
        options: { responsive: true }
    };

    if (!analyticsState.charts.forecast) {
        analyticsState.charts.forecast = createChart('forecastChart', config);
    } else {
        updateChart(analyticsState.charts.forecast, config);
    }

    const tableBody = document.querySelector('#increasingTypesTable tbody');
    tableBody.innerHTML = '';
    trends.increasing_types.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.label}</td>
            <td>${row.previous}</td>
            <td>${row.current}</td>
            <td>+${row.delta}</td>
        `;
        tableBody.appendChild(tr);
    });
}

function updateSummary(analytics) {
    const total = analytics.status.totals.reduce((sum, row) => sum + (parseInt(row.total, 10) || 0), 0);
    const resolved = analytics.status.totals.reduce((sum, row) => {
        if (row.status === 'resolved' || row.status === 'closed') {
            return sum + (parseInt(row.total, 10) || 0);
        }
        return sum;
    }, 0);
    const resolutionRate = total > 0 ? ((resolved / total) * 100).toFixed(1) : '0.0';

    document.getElementById('summaryTotal').textContent = formatNumber(total);
    document.getElementById('summaryResolutionRate').textContent = `${resolutionRate}%`;
    document.getElementById('summaryResponse').textContent = formatHours(analytics.response.avg_first_response_hours);
}

async function loadAnalytics() {
    const form = document.getElementById('analyticsFilterForm');
    const params = buildQueryParams(form);
    const url = `/CTS/admin/api/analytics.php${params ? `?${params}` : ''}`;

    const response = await fetch(url, { credentials: 'same-origin' });
    const data = await response.json();
    if (!data.success) {
        showNotification(data.error || 'Failed to load analytics', 'error');
        return;
    }

    analyticsState.data = data.analytics;
    updateVolumeCharts(data.analytics.volume);
    updateStatusCharts(data.analytics.status);
    updateResponseMetrics(data.analytics.response);
    updateTypeCharts(data.analytics.types);
    updateUnitPerformance(data.analytics.units);
    updateLocationChart(data.analytics.locations);
    updateForecast(data.analytics.trends, data.analytics.volume);
    updateSummary(data.analytics);
}

function bindUnitControls() {
    const buttons = document.querySelectorAll('.toggle-group button');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            analyticsState.view = button.dataset.view;
            if (analyticsState.data) {
                updateUnitPerformance(analyticsState.data.units);
            }
        });
    });

    const sortSelect = document.getElementById('unitSort');
    sortSelect.addEventListener('change', () => {
        analyticsState.sort = sortSelect.value;
        if (analyticsState.data) {
            updateUnitPerformance(analyticsState.data.units);
        }
    });

    // Cascading Office → Unit filter
    const officeSelect = document.getElementById('unitOfficeFilter');
    const unitSelect = document.getElementById('unitUnitFilter');

    if (officeSelect && unitSelect) {
        officeSelect.addEventListener('change', () => {
            const office = officeSelect.value;
            analyticsState.officeFilter = office;
            analyticsState.unitFilter = '';

            console.log('Office selected:', office);
            console.log('Available officeUnits:', window.analyticsConfig.officeUnits);

            // Reset and populate unit dropdown
            unitSelect.innerHTML = '';

            if (!office) {
                // No office selected — disable unit dropdown
                unitSelect.innerHTML = '<option value="">-- Select Office First --</option>';
                unitSelect.disabled = true;
            } else {
                // Office selected — populate with matching units
                const units = (window.analyticsConfig.officeUnits || {})[office] || [];
                console.log('Units for', office, ':', units);
                unitSelect.innerHTML = '<option value="">All Units</option>';
                units.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u;
                    opt.textContent = u;
                    unitSelect.appendChild(opt);
                });
                unitSelect.disabled = false;
            }

            if (analyticsState.data) {
                updateUnitPerformance(analyticsState.data.units);
            }
        });

        unitSelect.addEventListener('change', () => {
            analyticsState.unitFilter = unitSelect.value;
            if (analyticsState.data) {
                updateUnitPerformance(analyticsState.data.units);
            }
        });
    }
}

function bindFilters() {
    const form = document.getElementById('analyticsFilterForm');
    const resetBtn = document.getElementById('resetFiltersBtn');

    form.addEventListener('submit', event => {
        event.preventDefault();
        loadAnalytics();
    });

    // Ensure Enter key triggers form submission on all inputs and selects
    // Use capture phase to handle before admin.js generic handler
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                loadAnalytics();
            }
        }, true);
    });

    // Also handle keypress on the form level
    form.addEventListener('keypress', event => {
        if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
            event.preventDefault();
            event.stopPropagation();
            loadAnalytics();
        }
    }, true);

    resetBtn.addEventListener('click', () => {
        form.reset();
        document.getElementById('filterTargetDays').value = 14;
        loadAnalytics();
    });
}

function bindExports() {
    const csvBtn = document.getElementById('exportCsvBtn');
    const pdfBtn = document.getElementById('exportPdfBtn');
    const printBtn = document.getElementById('printReportBtn');
    const form = document.getElementById('analyticsFilterForm');

    csvBtn.addEventListener('click', () => {
        const params = buildQueryParams(form);
        window.location.href = `/CTS/admin/api/analytics-export.php${params ? `?${params}` : ''}`;
    });

    pdfBtn.addEventListener('click', () => {
        if (!analyticsState.data) {
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.setFontSize(14);
        doc.text('SDO CTS Analytics Summary', 14, 16);
        doc.setFontSize(10);
        doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 24);

        const summary = [
            ['Total Complaints', document.getElementById('summaryTotal').textContent],
            ['Resolution Rate', document.getElementById('summaryResolutionRate').textContent],
            ['Avg First Response', document.getElementById('summaryResponse').textContent]
        ];

        doc.autoTable({
            startY: 30,
            head: [['Metric', 'Value']],
            body: summary
        });

        const unitRows = [];
        analyticsState.data.units.received.slice(0, 10).forEach(row => {
            const unitLabel = window.analyticsConfig.unitLabels[row.unit] || row.unit;
            unitRows.push([unitLabel, row.total]);
        });

        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 10,
            head: [['Top Units (Received)', 'Complaints']],
            body: unitRows
        });

        doc.save('analytics_summary.pdf');
    });

    printBtn.addEventListener('click', () => {
        window.print();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindFilters();
    bindUnitControls();
    bindExports();
    loadAnalytics();
    bindTabs();
});

function bindTabs() {
    const tabs = document.querySelectorAll('.tab-button');
    const sections = () => Array.from(document.querySelectorAll('.dashboard-card')).map(section => ({
        id: section.id,
        top: section.offsetTop
    }));

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.dataset.target;
            const target = document.getElementById(targetId);
            if (target) {
                tabs.forEach(btn => btn.classList.remove('active'));
                tab.classList.add('active');
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', `#${targetId}`);
            }
        });
    });

    window.addEventListener('scroll', () => {
        const scrollPos = window.scrollY + 140;
        const list = sections();
        let activeId = list[0]?.id;
        for (const section of list) {
            if (scrollPos >= section.top) {
                activeId = section.id;
            }
        }
        if (activeId) {
            tabs.forEach(btn => btn.classList.toggle('active', btn.dataset.target === activeId));
        }
    }, { passive: true });
}
