$(document).ready(() => {
    $('#download').on('click', () => {
        $('#download').addClass('active');
        alert('Downloading is started!');
    });

    $('#downloadData').on('submit', function () {
        const downloadBtn = $('#download');
        downloadBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Downloading...');

        setTimeout(() => {
            downloadBtn.prop('disabled', false).html('Download');
            const modalEl = document.getElementById('downloadModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }, 2000);
    });
});
