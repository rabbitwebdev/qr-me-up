jQuery(document).ready(function($){
    $('#qrme-generator-form').on('submit', function(e){
        e.preventDefault();

        $('#qrme-result').html('<p>Generating your QR Code...</p>');

        $.post(qrme_ajax.ajax_url, {
            action: 'qrme_generate',
            name: $('input[name="name"]').val(),
            email: $('input[name="email"]').val(),
            url: $('input[name="url"]').val(),
            message: $('textarea[name="message"]').val()
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
