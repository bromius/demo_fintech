$(function () {
    const rates = {};
    const systemCurrency = window.systemCurrency;

    $('tbody').each(function() {
        const $tbody = $(this);
        $tbody.html(`
            <tr>
                <td colspan="${$tbody.closest('table').find('thead th').length}">
                    <div class="spinner-container">
                        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                    </div>
                </td>
            </tr>
        `);
    });

    Upload.init(function() {
        if ($.isEmptyObject(this.rates)) {
            Rates.init(function (rates) {
                rates = rates;
                Accounts.init(systemCurrency, self.rates);
            });
        } else {
            Accounts.init(systemCurrency, self.rates);
        }

        Chart.init();
        Transactions.init(() => {
             Accounts.init(systemCurrency, self.rates);
             Chart.init();
        });
    });

    Rates.init(function (rates) {
        rates = rates;
        Accounts.init(systemCurrency, self.rates);
    });

    Chart.init();

    Transactions.init(() => {
        Accounts.init(systemCurrency, self.rates);
        Chart.init();
    });
});