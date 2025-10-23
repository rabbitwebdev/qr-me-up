jQuery(document).ready(function($){
    $('#qrme-generator-form').on('submit', function(e){
        e.preventDefault();

        var formData = $(this).serialize();

        $('#qrme-result').html('<p>Generating your QR Code...</p>');

        $.post(qrme_ajax.ajax_url, {
            action: 'qrme_generate',
            ...Object.fromEntries(new URLSearchParams(formData))
        }, function(response){
            if(response.success){
                $('#qrme-result').html(`
                    <img src="${response.data.qr_url}" alt="QR Code" style="max-width:300px;display:block;margin-bottom:10px;">
                    <a href="${response.data.qr_url}" download="my_qr_code.png" class="qrme-download">Download QR Code</a>
                `);
            } else {
                $('#qrme-result').html('<p style="color:red;">Error generating QR Code.</p>');
            }
        });
    });
});
