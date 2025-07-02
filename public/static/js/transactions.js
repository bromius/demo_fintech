const Transactions = {
    onUpdateCallback: null,
    editor: null,
    transTable: null,
    transactionsData: [],
    currentEditing: null,

    init: function (onUpdateCallback) {
        this.onUpdateCallback = onUpdateCallback;
        this.loadData();
    },

    loadData: function () {
        $.get('/api/transactions', (response) => {
            if (!response || !response.success || !response.data) {
                return;
            }
            this.transactionsData = response.data;
            this.initDataTable();
        }).fail(function () {
            console.error('Failed to load transactions data');
        });
    },

    initDataTable: function () {
        this.initEditor();
        this.initTable();
        this.bindEvents();
    },

    initEditor: function () {
        this.editor = new DataTable.Editor({
            table: '#transTable',
            fields: [
                { label: 'Account', name: 'account' },
                { label: 'Transaction No', name: 'ident' },
                {
                    label: 'Amount',
                    name: 'amount',
                    type: 'text',
                    attr: { type: 'number', step: '0.01' }
                },
                { label: 'Currency', name: 'currency' },
                {
                    label: 'Date',
                    name: 'date',
                    type: 'datetime'
                }
            ],
            idSrc: 'id',
            formOptions: {
                main: {
                    focus: null,
                    title: null
                }
            },
            ajax: (method, url, data, success) => {
                this.handleEditorAjax(data, success);
            }
        });
    },

    handleEditorAjax: function (data, success) {
        const action = data.action;

        if (action === 'edit') {
            const id = Object.keys(data.data)[0];
            const update = data.data[id];

            if (!update) return;

            $.ajax({
                url: `/api/transactions/update/${id}`,
                type: 'PATCH',
                data: update,
                success: () => {
                    const row = this.transactionsData.find(r => r.id == id);
                    if (row) {
                        Object.assign(row, update);
                        success({ data: [row] });
                    }
                },
                error: () => {
                    alert('Failed to update transaction');
                }
            });
        }
    },

    initTable: function () {
        this.transTable = $('#transTable').DataTable({
            destroy: true,
            data: this.transactionsData,
            dom: `
            <"d-flex justify-content-between align-items-center mb-3"
                <"d-flex align-items-center"l>
                <"d-flex align-items-center"p>
            >
            <"export-container d-flex align-items-center mb-2">B
            t
            `,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columns: [
                { data: 'account' },
                { data: 'ident', className: 'editable' },
                {
                    data: 'amount',
                    className: 'editable text-end',
                    render: function (data) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { data: 'currency' },
                {
                    data: 'date',
                    className: 'editable',
                    type: 'datetime'
                },
                {
                    data: null,
                    orderable: false,
                    defaultContent: '<button class="btn btn-sm"><i class="bi bi-trash"></i></button>'
                }
            ],
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-default btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-default btn-sm'
                }
            ],
            language: {
                lengthMenu: "Show _MENU_ entries",
                info: "",
                search: ""
            }
        });
        $('.export-container').prepend('<span class="me-3 mt-1">Export full table</span>');
    },

    bindEvents: function () {
        const self = this;

        $('#transTable').on('click', 'td.editable', function (e) {
            const cell = $(this);

            if (cell.find('input').length > 0) {
                // Click inside already editing input - do nothing to preserve cursor position
                return;
            }

            // Cancel any existing editing immediately before starting new one
            if (self.currentEditing) {
                self.cancelCurrentEditing();
            }

            const row = self.transTable.row(cell.parent());
            const rowData = row.data();
            const field = self.transTable.settings()[0].aoColumns[cell.index()].data;
            const originalValue = rowData[field];

            let input;
            if (field === 'amount') {
                input = $('<input>', {
                    type: 'number',
                    class: 'form-control form-control-sm text-end',
                    step: '0.01',
                    value: parseFloat(originalValue)
                });
            } else if (field === 'date') {
                // For date, show raw backend string truncated to "YYYY-MM-DDTHH:mm"
                // backend format: "2025-06-27 14:00:00"
                // convert to "2025-06-27T14:00" for datetime-local input without timezone conversion
                let datetimeValue = '';
                if (originalValue && typeof originalValue === 'string') {
                    datetimeValue = originalValue.replace(' ', 'T').slice(0, 16);
                }
                input = $('<input>', {
                    type: 'datetime-local',
                    class: 'form-control form-control-sm',
                    value: datetimeValue
                });
            } else {
                input = $('<input>', {
                    type: 'text',
                    class: 'form-control form-control-sm',
                    value: originalValue
                });
            }

            cell.html(input);
            input.focus();

            const inputEl = input[0];
            setTimeout(() => {
                try {
                    const length = inputEl.value.length;
                    inputEl.setSelectionRange(length, length);
                } catch (ex) {
                    // Ignore DOMException if input is not focusable anymore
                }
            }, 0);

            self.currentEditing = {
                cell: cell,
                input: input,
                row: row,
                rowData: rowData,
                field: field,
                originalValue: originalValue
            };

            input.on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    self.saveCurrentEditing();
                } else if (e.key === 'Escape') {
                    self.cancelCurrentEditing();
                }
            });

            input.one('blur', function () {
                if (self.currentEditing && self.currentEditing.input[0] === inputEl) {
                    self.saveCurrentEditing();
                }
            });
        });

        $('#transTable').on('click', '.bi-trash', function (e) {
            e.stopPropagation();
            const row = self.transTable.row($(this).closest('tr'));
            const rowData = row.data();
            const id = rowData.id;

            if (!confirm('Are you sure you want to delete this transaction?')) return;

            $.ajax({
                url: `/api/transactions/delete/${id}`,
                type: 'DELETE',
                success: () => {
                    self.transactionsData = self.transactionsData.filter(r => r.id != id);
                    self.transTable.row(row)
                        .remove()
                        .draw();
                        
                    if (self.onUpdateCallback) {
                        self.onUpdateCallback();
                    }
                },
                error: () => {
                    alert('Failed to delete transaction');
                }
            });
        });
    },

    saveCurrentEditing: function () {
        const self = this;

        if (!this.currentEditing) return;

        const { cell, input, row, rowData, field, originalValue } = this.currentEditing;
        let newValue = input.val();
        let isValid = true;

        if (field === 'amount') {
            if (newValue.trim() === '') {
                newValue = '0';
            }
            if (isNaN(parseFloat(newValue))) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                newValue = parseFloat(newValue);
                input.removeClass('is-invalid');
            }
        } else if (field === 'date') {
            // newValue is in "YYYY-MM-DDTHH:mm" format from datetime-local input
            // We must send to backend exactly in "Y-m-d H:i:00" format without timezone conversion
            if (!newValue) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                // Convert "YYYY-MM-DDTHH:mm" to "YYYY-MM-DD HH:mm:00" string literally
                newValue = newValue.replace('T', ' ') + ':00';
                input.removeClass('is-invalid');
            }
        } else {
            newValue = newValue.trim();
        }

        // If value unchanged, just cancel editing without sending request
        if (field === 'date') {
            // Compare raw strings without timezone conversion
            if (originalValue === newValue) {
                this.cancelCurrentEditing();
                return;
            }
        } else if (newValue == originalValue ||
            (field === 'amount' && parseFloat(newValue) === parseFloat(originalValue))) {
            this.cancelCurrentEditing();
            return;
        }

        if (!isValid) {
            return;
        }

        const update = {};
        update[field] = newValue;

        $.ajax({
            url: `/api/transactions/update/${rowData.id}`,
            type: 'PATCH',
            data: update,
            success: () => {
                rowData[field] = newValue;
                self.transTable.cell(cell).data(newValue).draw(false);
                cell.html(self.formatCellValue(field, newValue));
                self.currentEditing = null;

                if (self.onUpdateCallback) {
                    self.onUpdateCallback();
                }
            },
            error: () => {
                input.addClass('is-invalid');
                self.cancelCurrentEditing();
            }
        });
    },

    cancelCurrentEditing: function () {
        if (!this.currentEditing) return;

        const { cell, field, originalValue } = this.currentEditing;
        cell.html(this.formatCellValue(field, originalValue));
        this.currentEditing = null;
    },

    formatCellValue: function (field, value) {
        if (field === 'amount') {
            return parseFloat(value).toFixed(2);
        }
        if (field === 'date') {
            // Show raw string as is without conversion
            return value || '';
        }
        return value;
    }
}