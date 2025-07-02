<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Transactions</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="https://cdn.datatables.net/select/1.6.2/css/select.bootstrap5.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="https://cdn.datatables.net/datetime/1.4.0/css/dataTables.dateTime.min.css" rel="stylesheet" crossorigin="anonymous" />

    <link rel="stylesheet" href="https://cdn.datatables.net/v/dt/jqc-1.12.4/dt-2.3.2/b-3.2.3/sl-3.0.1/datatables.min.css" />

    <link href="/static/css/app.css" rel="stylesheet" />
</head>

<body>
    <div class="container my-4">
        <div class="row mb-4 pb-4">
            <div class="col-lg-8">
                <h2 class="section-title fs-6">Upload</h2>
                <div class="card h-100">
                    <div class="card-body p-5">
                        <div class="row">
                            <div class="col-12 col-sm-4 d-flex align-items-start mb-3">
                                <button id="browseBtn" type="button" class="btn btn-success btn-sm flex-shrink-0">Browse files</button>
                                <span id="fileLabel" class="text-muted d-flex align-items-center mx-2 mt-1">
                                    File <i class="bi bi-info-circle ms-1"></i>
                                </span>
                            </div>
                            <div class="col-12 col-sm-8 drop-zone d-none d-md-flex" id="dropZone">
                                <i class="bi bi-download me-2" style="font-size: 2rem; color: #6c757d;"></i>
                                <span class="text-muted">Click the file &amp; drop it here (drag & drop)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <h2 class="section-title fs-6">Currency exchange rates</h2>
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted">Current FX rates</h6>
                        <h6 class="text-muted fs-6">Value in <?= $systemCurrency ?></h6>
                        <div class="table-responsive table-responsive-sm">
                            <table id="fxTable" class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Currency</th>
                                        <th class="text-end">FX Rate</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title fs-6">List of bank accounts</h2>
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive table-responsive-sm">
                    <table id="accountsTable" class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th width="20%">Bank</th>
                                <th width="20%">Currency</th>
                                <th width="20%" class="text-end">Starting balance</th>
                                <th width="20%" class="text-end">End balance</th>
                                <th width="20%" class="text-end">End balance (<?= $systemCurrency ?>)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <h2 class="section-title fs-6">Cash forecast</h2>
        <div class="card mb-4">
            <div class="card-body">
                <div id="chartContainer" style="height: 25rem;">
                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                </div>
            </div>
        </div>

        <h2 class="section-title fs-6">Transactions</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive table-responsive-sm">
                    <table id="transTable" class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Transaction No</th>
                                <th class="text-end">Amount</th>
                                <th>Currency</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>

    <script src="https://code.highcharts.com/highcharts.js" crossorigin="anonymous"></script>
    <script src="https://code.highcharts.com/12.3.0/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/12.3.0/modules/offline-exporting.js"></script>
    <script src="https://code.highcharts.com/12.3.0/modules/export-data.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/datetime/1.4.0/js/dataTables.dateTime.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/select/1.6.2/js/dataTables.select.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/select/1.6.2/js/select.bootstrap5.min.js" crossorigin="anonymous"></script>

    <script src="/node_modules/@datatables.net/editor-2025-07-13/js/dataTables.editor.min.js"></script>
    <script src="/node_modules/@datatables.net/editor-2025-07-13-dt/js/editor.dataTables.js"></script>

    <script src="/static/js/upload.js" type="text/javascript"></script>
    <script src="/static/js/rates.js" type="text/javascript"></script>
    <script src="/static/js/accounts.js" type="text/javascript"></script>
    <script src="/static/js/chart.js" type="text/javascript"></script>
    <script src="/static/js/transactions.js" type="text/javascript"></script>

    <script type="text/javascript">
        window.systemCurrency = '<?= $systemCurrency ?>';
    </script>
    <script src="/static/js/app.js" type="text/javascript"></script>
</body>

</html>