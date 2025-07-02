const Rates = {
    init: function (cb) {
        $.get('/api/rates', function (response) {
            if (!response || !response.success || !response.data) {
                return;
            }

            const t = $('#fxTable tbody').empty();

            Object.keys(response.data).forEach(function (currency) {
                const rate = parseFloat(response.data[currency]);
                t.append(
                    '<tr>' +
                    '<td>' + currency + '</td>' +
                    '<td class="text-end">' + rate.toFixed(4) + '</td>' +
                    '</tr>'
                );
            });

            cb && cb(response.data);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert('Failed to fetch rates');
        });
    }
};