import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

const chartDataElement = document.getElementById('dashboard-chart-data');

if (chartDataElement) {
    const parsedData = JSON.parse(chartDataElement.textContent || '{}');

    const renderChart = (selector, options) => {
        const element = document.querySelector(selector);

        if (!element) {
            return null;
        }

        const chart = new ApexCharts(element, options);
        chart.render();

        return chart;
    };

    const periodOptions = parsedData.periodOptions || {
        weekly: 'Weekly',
        monthly: 'Monthly',
        yearly: 'Yearly',
    };

    const getPeriodData = (dataset, period) => {
        if (!dataset) {
            return { labels: [], series: [] };
        }

        if (dataset[period]) {
            return dataset[period];
        }

        if (dataset.weekly) {
            return dataset.weekly;
        }

        return {
            labels: dataset.labels || [],
            series: dataset.series || [],
        };
    };

    const renderSparkline = (card) => {
        renderChart(`#${card.sparkline_id}`, {
            chart: {
                type: card.sparkline_type,
                height: 106,
                sparkline: {
                    enabled: true,
                },
                toolbar: {
                    show: false,
                },
            },
            series: [
                {
                    data: card.sparkline,
                },
            ],
            stroke: {
                width: 2,
                curve: 'smooth',
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    opacityFrom: 0.42,
                    opacityTo: 0.08,
                    stops: [0, 100],
                },
            },
            yaxis: {
                min: 0,
            },
            plotOptions: {
                bar: {
                    borderRadius: 3,
                    columnWidth: '30%',
                },
            },
            markers: {
                size: 0,
            },
            colors: [card.sparkline_color],
            tooltip: {
                fixed: {
                    enabled: false,
                },
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter() {
                            return '';
                        },
                    },
                },
                marker: {
                    show: false,
                },
            },
        });
    };

    const renderInquiriesChart = () => {
        const inquiries = getPeriodData(parsedData.inquiries, 'weekly');

        return renderChart('#datalabels-column2', {
            chart: {
                height: 280,
                type: 'bar',
                toolbar: {
                    show: false,
                },
            },
            plotOptions: {
                bar: {
                    borderRadius: 2,
                    columnWidth: '30%',
                    dataLabels: {
                        position: 'top',
                    },
                },
            },
            dataLabels: {
                enabled: true,
                formatter(value) {
                    return Math.round(value);
                },
                offsetY: -22,
                style: {
                    fontSize: '12px',
                    colors: ['#304758'],
                },
            },
            colors: ['#4d5761'],
            legend: {
                show: false,
            },
            series: [
                {
                    name: 'Total Inquiries',
                    data: inquiries.series || [],
                },
            ],
            xaxis: {
                categories: inquiries.labels || [],
                position: 'bottom',
                axisBorder: {
                    show: true,
                },
                axisTicks: {
                    show: true,
                },
            },
            yaxis: {
                axisBorder: {
                    show: true,
                },
                axisTicks: {
                    show: true,
                },
                labels: {
                    show: true,
                    formatter(value) {
                        return value;
                    },
                },
            },
            grid: {
                row: {
                    colors: ['transparent', 'transparent'],
                    opacity: 0.2,
                },
                borderColor: '#f1f3fa',
            },
        });
    };

    const renderServiceHeatmap = () => {
        const serviceHeatmap = getPeriodData(parsedData.serviceHeatmap, 'weekly');

        return renderChart('#basic-heatmap', {
            chart: {
                toolbar: {
                    show: false,
                },
                height: 280,
                type: 'heatmap',
            },
            dataLabels: {
                enabled: false,
            },
            colors: ['#22B956'],
            series: serviceHeatmap.series || [],
            xaxis: {
                type: 'category',
                categories: serviceHeatmap.labels || [],
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '11px',
                    },
                },
            },
        });
    };

    const renderVisitorDevicesChart = () => {
        const visitorDevices = parsedData.visitorDevices || { labels: [], series: [] };
        const series = Array.isArray(visitorDevices.series)
            ? visitorDevices.series.map((value) => Number(value) || 0)
            : [];
        const hasData = series.some((value) => value > 0);

        return renderChart('#simple-donut', {
            chart: {
                height: 280,
                type: 'donut',
            },
            series: hasData ? series : [1],
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                verticalAlign: 'middle',
                floating: false,
                fontSize: '14px',
                offsetX: 0,
                offsetY: 7,
            },
            labels: hasData ? (visitorDevices.labels || []) : ['No GA data'],
            colors: hasData ? ['#22B956', '#1989df', '#f59e0b'] : ['#64748b'],
            responsive: [{
                breakpoint: 600,
                options: {
                    chart: {
                        height: 240,
                    },
                    legend: {
                        show: false,
                    },
                },
            }],
        });
    };

    const setActivePeriodButton = (chartKey, period) => {
        document.querySelectorAll(`[data-dashboard-chart-period="${chartKey}"]`).forEach((button) => {
            button.classList.toggle('active', button.dataset.period === period);
        });

        document.querySelectorAll(`[data-dashboard-period-label="${chartKey}"]`).forEach((label) => {
            label.textContent = periodOptions[period] || period;
        });
    };

    const bindChartPeriodControls = (charts) => {
        document.querySelectorAll('[data-dashboard-chart-period]').forEach((button) => {
            button.addEventListener('click', () => {
                const chartKey = button.dataset.dashboardChartPeriod;
                const period = button.dataset.period || 'weekly';

                if (chartKey === 'inquiries' && charts.inquiries) {
                    const inquiries = getPeriodData(parsedData.inquiries, period);

                    charts.inquiries.updateOptions({
                        xaxis: {
                            categories: inquiries.labels || [],
                        },
                    }, false, true);

                    charts.inquiries.updateSeries([
                        {
                            name: 'Total Inquiries',
                            data: inquiries.series || [],
                        },
                    ]);
                }

                if (chartKey === 'serviceHeatmap' && charts.serviceHeatmap) {
                    const serviceHeatmap = getPeriodData(parsedData.serviceHeatmap, period);

                    charts.serviceHeatmap.updateOptions({
                        xaxis: {
                            type: 'category',
                            categories: serviceHeatmap.labels || [],
                        },
                    }, false, true);

                    charts.serviceHeatmap.updateSeries(serviceHeatmap.series || []);
                }

                setActivePeriodButton(chartKey, period);
            });
        });
    };

    const setupEmailDeliveryFilter = () => {
        const rows = Array.from(document.querySelectorAll('[data-dashboard-email-row]'));
        const buttons = Array.from(document.querySelectorAll('.dashboard-email-period-option'));
        const label = document.getElementById('dashboard-email-period-label');
        const emptyRow = document.getElementById('dashboard-email-empty-row');

        if (!rows.length || !buttons.length) {
            return;
        }

        const startForPeriod = (period) => {
            const start = new Date();
            start.setHours(0, 0, 0, 0);

            if (period === 'weekly') {
                start.setDate(start.getDate() - 6);
            }

            if (period === 'monthly') {
                start.setDate(start.getDate() - 29);
            }

            return start.getTime();
        };

        const applyFilter = (period) => {
            const start = startForPeriod(period);
            let visibleCount = 0;

            rows.forEach((row) => {
                const createdAt = Number(row.dataset.created || 0) * 1000;
                const isVisible = createdAt >= start;

                row.classList.toggle('d-none', !isVisible);

                if (isVisible) {
                    visibleCount += 1;
                }
            });

            if (emptyRow) {
                emptyRow.classList.toggle('d-none', visibleCount !== 0);
            }

            buttons.forEach((button) => {
                button.classList.toggle('active', button.dataset.period === period);
            });

            if (label) {
                const activeButton = buttons.find((button) => button.dataset.period === period);
                label.textContent = activeButton ? activeButton.textContent.trim() : 'Today';
            }
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => applyFilter(button.dataset.period || 'today'));
        });

        applyFilter('today');
    };

    (parsedData.cards || []).forEach(renderSparkline);
    const charts = {
        inquiries: renderInquiriesChart(),
        serviceHeatmap: renderServiceHeatmap(),
        visitorDevices: renderVisitorDevicesChart(),
    };

    bindChartPeriodControls(charts);
    setupEmailDeliveryFilter();
}
