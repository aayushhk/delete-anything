jQuery(function ($) {
    $('#dcp-confirm-delete').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Deleting...');

        $.post(DCP.ajax, {
            action: 'dcp_delete_post',
            nonce: DCP.nonce,
            post_id: DCP.post_id
        }).done(function (res) {
            if (res.success) {
                window.location.href = DCP.redirect;
            } else {
                alert(res.data || 'Delete failed');
                $btn.prop('disabled', false).text('Confirm Delete');
            }
        });
    });
});
