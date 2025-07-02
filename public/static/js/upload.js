const Upload = {
    successfulUploadCallback: undefined,

    init: function (successfulUploadCallback) {
        const self = this;

        if (successfulUploadCallback) {
            this.successfulUploadCallback = successfulUploadCallback;
        }

        $('#browseBtn').on('click', function () {
            $('<input type="file" id="fileInput" accept=".xlsx,.csv">')
                .on('change', function (e) {
                    if (this.files.length > 0) {
                        $('#fileLabel').text(this.files[0].name)
                            .removeClass('ms-3');
                        self.uploadFile(this.files[0]);
                    }
                })
                .click();
        });

        $('#dropZone')
            .on('dragover', function (e) {
                e.preventDefault();
                $(this).addClass('dragover');
            })
            .on('dragleave drop', function (e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                if (e.type == 'drop' && e.originalEvent.dataTransfer.files.length > 0) {
                    $('#fileLabel').text(e.originalEvent.dataTransfer.files[0].name)
                        .removeClass('ms-3');
                    self.uploadFile(e.originalEvent.dataTransfer.files[0]);
                }
            });
    },

    uploadFile: function (file) {
        const self = this;

        const formData = new FormData();
        formData.append('file', file);

        $.ajax({
            url: '/api/transactions/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#dropZone').addClass('uploading');
                $('#fileLabel').html('<i class="bi bi-arrow-repeat spin"></i> Uploading...');
            },
            success: function (response) {
                if (self.successfulUploadCallback) {
                    self.successfulUploadCallback();
                }
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : 'Error uploading file';
                alert('Error: ' + errorMsg);
            },
            complete: function () {
                $('#dropZone').removeClass('uploading');
                $('#fileLabel').text(file.name);
            }
        });
    }
};