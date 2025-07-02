const Chart = {
    chart: null,
    rawData: [],
    processedData: {},
    minDate: null,
    maxDate: null,
    chartCategories: [],
    chartSeries: [],

    init: function() {
        this.loadData();
    },

    loadData: function() {
        $.get('/api/chart', (response) => {
            if (!response || !response.success || !response.data) {
                return;
            }
            this.rawData = response.data;
            this.processData();
            this.renderChart();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert('Failed to fetch chart data');
        });
    },

    processData: function() {
        if (this.rawData.length === 0) return;

        this.minDate = new Date(this.rawData[0].date);
        this.maxDate = new Date(this.rawData[0].date);

        this.rawData.forEach(item => {
            const date = new Date(item.date);
            if (date < this.minDate) this.minDate = date;
            if (date > this.maxDate) this.maxDate = date;
        });

        this.processedData = {};
        this.rawData.forEach(item => {
            if (!this.processedData[item.account]) {
                this.processedData[item.account] = [];
            }
            this.processedData[item.account].push({
                date: item.date,
                amount: parseFloat(item.amount)
            });
        });

        this.prepareSeriesData();
    },

    prepareSeriesData: function() {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        this.chartCategories = [];
        const date = new Date(this.minDate);
        
        while (date <= this.maxDate) {
            this.chartCategories.push(`${monthNames[date.getMonth()]} ${date.getFullYear().toString().slice(2)}`);
            date.setMonth(date.getMonth() + 1);
        }

        const allAccountsData = new Array(this.chartCategories.length).fill(0);

        this.chartSeries = Object.keys(this.processedData).map(account => {
            const accountData = this.processedData[account];
            const data = new Array(this.chartCategories.length).fill(0);

            accountData.forEach(transaction => {
                const transactionDate = new Date(transaction.date);
                const monthDiff = (transactionDate.getFullYear() - this.minDate.getFullYear()) * 12 + 
                    transactionDate.getMonth() - this.minDate.getMonth();
                if (monthDiff >= 0 && monthDiff < data.length) {
                    const amount = parseFloat(transaction.amount);
                    data[monthDiff] = parseFloat((data[monthDiff] + amount).toFixed(2));
                    allAccountsData[monthDiff] = parseFloat((allAccountsData[monthDiff] + amount).toFixed(2));
                }
            });

            return {
                name: account,
                data: data,
                visible: true
            };
        });

        this.chartSeries.push({
            name: 'All Accounts',
            data: allAccountsData,
            visible: true,
            color: '#6c757d'
        });
    },

    renderChart: function() {
        this.chart = Highcharts.chart('chartContainer', {
            chart: {
                type: 'line'
            },
            title: {
                text: null
            },
            xAxis: {
                categories: this.chartCategories
            },
            yAxis: {
                title: {
                    text: 'Balance'
                },
                labels: {
                    formatter: function() {
                        return this.value.toFixed(2);
                    }
                }
            },
            tooltip: {
                pointFormatter: function() {
                    return `<span style="color:${this.color}">\u25CF</span> ${this.series.name}: <b>${this.y.toFixed(2)}</b><br/>`;
                },
                valueDecimals: 2
            },
            legend: {
                itemEvents: {
                    click: function(e) {
                        e.preventDefault();
                        const series = this.chart.get(this.options.id);
                        if (series) {
                            series.setVisible(!series.visible, false);
                            this.chart.redraw();
                        }
                    }
                }
            },
            plotOptions: {
                series: {
                    events: {
                        legendItemClick: function(e) {
                            return false;
                        }
                    },
                    dataLabels: {
                        enabled: false,
                        formatter: function() {
                            return this.y.toFixed(2);
                        }
                    }
                },
                line: {
                    marker: {
                        enabled: false
                    }
                }
            },
            series: this.chartSeries,
            exporting: {
                enabled: true,
                buttons: {
                    contextButton: {
                        menuItems: ['downloadPNG', 'downloadPDF', 'downloadSVG']
                    }
                },
                chartOptions: {
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return this.y && typeof this.y.toFixed === 'function' ? this.y.toFixed(2) : '0.00';
                                }
                            }
                        }
                    }
                }
            },
            accessibility: { 
                enabled: false 
            }
        });
    }
};