const Accounts = {
    systemCurrency: null,
    rates: {},
    accountsTable: null,
    savedBalances: {},

    init: function(systemCurrency, rates) {
        if (systemCurrency) {
            this.systemCurrency = systemCurrency;
        }

        if (rates) {
            this.rates = rates;
        }

        this.loadAccountsData();
    },

    loadAccountsData: function() {
        const self = this;

        $.get('/api/accounts', function (response) {
            if (!response || !response.success || !response.data) {
                return;
            }

            $('#accountsTable tbody').empty();

            self.renderAccounts(response.data);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert('Failed to fetch accounts');
        });
    },

    renderAccounts: function(apiData) {
        const tableData = this.prepareTableData(apiData);
        const totalRow = this.calculateTotalRow(tableData);
        const dataWithTotal = [totalRow].concat(tableData);

        this.initDataTable(dataWithTotal);
        this.setupEditHandlers();
    },

    prepareTableData: function(apiData) {
        return apiData.map(account => {
            const accountId = account.account;
            const currency = account.currency;
            const apiEndBalance = parseFloat(account.end_balance) || 0;
            const startBalance = this.savedBalances[accountId] || 0;
            const endBalance = startBalance + apiEndBalance;
            const rate = currency === this.systemCurrency ? 1 : (this.rates[currency] || 1);
            const endBalanceSystemCurrency = endBalance * rate;

            return {
                account: accountId,
                currency: currency,
                start: startBalance,
                api_end_balance: apiEndBalance,
                end_balance: endBalance,
                end_balance_system_currency: endBalanceSystemCurrency,
                editable: true,
                rate: rate
            };
        });
    },

    calculateTotalRow: function(tableData) {
        return {
            account: 'Total',
            currency: '',
            start: tableData.reduce((sum, row) => sum + row.start, 0),
            end_balance: tableData.reduce((sum, row) => sum + row.end_balance, 0),
            end_balance_system_currency: tableData.reduce((sum, row) => sum + row.end_balance_system_currency, 0),
            editable: false,
            isTotal: true
        };
    },

    initDataTable: function(data) {
        if (this.accountsTable) {
            this.accountsTable.destroy();
        }

        this.accountsTable = $('#accountsTable').DataTable({
            data: data,
            columns: this.getTableColumns(),
            paging: false,
            info: false,
            searching: false,
            order: [],
            createdRow: function(row, data) {
                if (data.isTotal) {
                    $(row).addClass('total-row');
                }
            },
            drawCallback: function() {
                const api = this.api();
                const totalRow = $('.total-row').detach();
                $(api.table().body()).prepend(totalRow);
            },
            ordering: function(settings, col) {
                // Disable Total row ordering
                return !$(settings.aoData[col].nTr).hasClass('total-row');
            }
        });
    },

    getTableColumns: function() {
        return [
            {
                data: 'account',
                render: (data, type, row) => this.renderEditableCell(data, type, row, 'account')
            },
            { 
                data: 'currency',
                render: data => this.renderNonEditableCell(data)
            },
            {
                data: 'start',
                className: 'text-end',
                render: (data, type, row) => this.renderEditableCell(data, type, row, 'start', true)
            },
            {
                data: 'end_balance',
                className: 'text-end',
                render: data => this.renderNonEditableCell(data, true)
            },
            {
                data: 'end_balance_system_currency',
                className: 'text-end',
                render: data => this.renderNonEditableCell(data, true)
            }
        ];
    },

    renderEditableCell: function(data, type, row, cellType, isNumber = false) {
        if (type === 'display') {
            const displayValue = isNumber 
                ? $.fn.dataTable.render.number(',', '.', 2).display(data)
                : data;
            
            return row.editable 
                ? `<span class="editable-${cellType}">${displayValue}</span>`
                : `<span class="non-editable">${displayValue}</span>`;
        }
        return data;
    },

    renderNonEditableCell: function(data, isNumber = false) {
        const displayValue = isNumber 
            ? $.fn.dataTable.render.number(',', '.', 2).display(data)
            : data;
        
        return `<span class="non-editable">${displayValue}</span>`;
    },

    setupEditHandlers: function() {
        const self = this;
        
        $('#accountsTable').on('click', '.editable-account', function(e) {
            self.handleAccountEdit.call(self, $(this), e);
        });
        
        $('#accountsTable').on('click', '.editable-start', function(e) {
            self.handleStartEdit.call(self, $(this), e);
        });
    },

    handleAccountEdit: function(element, e) {
        const td = element.closest('td');
        const row = this.accountsTable.row(td);
        const rowData = row.data();
        
        if (!rowData.editable) return;

        const input = $('<input type="text" class="form-control form-control-sm">')
            .val(rowData.account);
        
        td.html(input);
        input.focus();

        const handleUpdate = () => {
            const newValue = input.val().trim();
            
            if (newValue === '') {
                input.addClass('is-invalid');
                setTimeout(() => {
                    td.html(`<span class="editable-account">${rowData.account}</span>`);
                }, 2000);
                return;
            }

            rowData.account = newValue;
            row.data(rowData).draw();
        };

        input.on('blur', handleUpdate);
        input.on('keydown', function(e) {
            if (e.key === 'Enter') handleUpdate();
        });
    },

    handleStartEdit: function(element, e) {
        const td = element.closest('td');
        const row = this.accountsTable.row(td);
        const rowData = row.data();
        
        if (!rowData.editable) return;

        const input = $('<input type="number" step="0.01" class="form-control form-control-sm text-end">')
            .val(rowData.start);
        
        td.html(input);
        input.focus();

        const handleUpdate = () => {
            const newValue = parseFloat(input.val()) || 0;
            this.savedBalances[rowData.account] = newValue;
            
            rowData.start = newValue;
            rowData.end_balance = newValue + rowData.api_end_balance;
            rowData.end_balance_system_currency = rowData.end_balance * rowData.rate;
            
            row.data(rowData).draw();
            this.updateTotalRow();
        };

        input.on('blur', handleUpdate);
        input.on('keydown', function(e) {
            if (e.key === 'Enter') handleUpdate();
        });
    },

    updateTotalRow: function() {
        const allData = this.accountsTable.rows().data().toArray();
        const tableData = allData.slice(1);
        const totalRow = this.calculateTotalRow(tableData);
        this.accountsTable.row(0).data(totalRow).draw();
    }
};