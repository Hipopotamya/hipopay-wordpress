jQuery(document).ready(function($) {
    $('.hipopay-public-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('.hipopay-form-message');

        $btn.attr('disabled', 'disabled').text('Lütfen bekleyin...');
        $msg.hide();

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success && response.data.redirect) {
                    $msg.html('<span style="color:green;">Yönlendiriliyorsunuz...</span>').show();
                    window.location.href = response.data.redirect;
                } else {
                    $msg.html('<span style="color:red;">' + response.data + '</span>').show();
                    $btn.removeAttr('disabled').text('Ödeme Yap');
                }
            },
            error: function() {
                $msg.html('<span style="color:red;">Sunucu bağlantı hatası.</span>').show();
                $btn.removeAttr('disabled').text('Ödeme Yap');
            }
        });
    });
});
