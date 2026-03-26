jQuery(document).ready(function($) {

    /* ── API Bağlantı Testi ─────────────────────────────────── */
    $('#hipopay-test-connection').on('click', function(e) {
        e.preventDefault();

        var apiKey    = $('#api_key').val();
        var apiSecret = $('#api_secret').val();
        var resultSpan = $('#hipopay-test-result');

        if (!apiKey || !apiSecret) {
            resultSpan.html('<span style="color:red">Lütfen önce anahtarları kaydedin.</span>');
            return;
        }

        resultSpan.html('<span>Bağlanılıyor...</span>');

        $.ajax({
            url:  hipopay_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action:     'hipopay_test_connection',
                nonce:      hipopay_admin_ajax.nonce,
                api_key:    apiKey,
                api_secret: apiSecret
            },
            success: function(response) {
                if (response.success) {
                    resultSpan.html('<span style="color:green;font-weight:bold;">' + response.data.message + '</span>');
                } else {
                    resultSpan.html('<span style="color:red;font-weight:bold;">' + response.data.message + '</span>');
                }
            },
            error: function() {
                resultSpan.html('<span style="color:red;font-weight:bold;">Sunucuya bağlanılamadı.</span>');
            }
        });
    });


});
