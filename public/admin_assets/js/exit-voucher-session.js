$(document).ready(function () {
    const token = $('meta[name="csrf-token"]').attr('content');
    const selectedSessionItemId =
        window.selectedSessionItemId || null;

    const academicSessionId =
        $('#exit_academic_session_id').val();

    if (academicSessionId) {
        loadSessionItems(
            window.classSectionUrl,
            token,
            academicSessionId,
            selectedSessionItemId
        );
    }

    $('#exit_academic_session_id').on('change', function () {
        const academicSessionId = $(this).val();

        $('#session_item_id').html(
            '<option value="">Select</option>'
        );

        resetVoucherFields();

        if (!academicSessionId) {
            return;
        }

        loadSessionItems(
            window.classSectionUrl,
            token,
            academicSessionId
        );
    });

    $('#session_item_id').on('change', function () {
        const sessionItemId = $(this).val();
        const academicSessionId =
            $('#exit_academic_session_id').val();

        resetVoucherFields();

        if (!academicSessionId || !sessionItemId) {
            return;
        }

        loadVoucherAmounts(
            academicSessionId,
            sessionItemId,
            token
        );
    });
});


function loadSessionItems(
    routeUrl,
    token,
    academicSessionId,
    selectedSessionItemId = null
) {
    $.ajax({
        url: routeUrl,
        method: 'POST',
        dataType: 'json',
        data: {
            _token: token,
            academic_session_id: academicSessionId
        },
        success: function (response) {
            $('#session_item_id').html(
                '<option value="">Select</option>'
            );

            if (response.status_code !== 200) {
                return;
            }

            $.each(response.session_items, function (index, item) {
                let selected = '';

                if (
                    selectedSessionItemId &&
                    String(item.id) === String(selectedSessionItemId)
                ) {
                    selected = 'selected';
                }

                if (
                    !selectedSessionItemId &&
                    response.session_items.length === 1
                ) {
                    selected = 'selected';
                }

                $('#session_item_id').append(
                    '<option value="' +
                    item.id +
                    '" ' +
                    selected +
                    '>' +
                    'Class ' +
                    item.class +
                    ' - Section ' +
                    item.section +
                    '</option>'
                );
            });

            const sessionItemId =
                $('#session_item_id').val();

            if (sessionItemId) {
                loadVoucherAmounts(
                    academicSessionId,
                    sessionItemId,
                    token
                );
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);

            $('#session_item_id').html(
                '<option value="">Select</option>'
            );
        }
    });
}


function loadVoucherAmounts(
    academicSessionId,
    sessionItemId,
    token
) {
    $.ajax({
        url: window.voucherAmountsUrl,
        method: 'POST',
        dataType: 'json',
        data: {
            _token: token,
            academic_session_id: academicSessionId,
            session_item_id: sessionItemId
        },
        success: function (response) {
            resetAmounts();

            $('#due_date').val('');
            $('#notes').val('');

            if (response.status_code !== 200) {
                return;
            }

            if (!response.voucher_exists) {
                if ($('#total_amount').length) {
                    $('#total_amount').val('0.00');
                }

                $('.fee-amount').prop('readonly', false);
                return;
            }

            $.each(response.voucher_items, function (index, item) {
                $('.fee-amount').each(function () {
                    const feeName = $(this).data('fee-name');

                    if (feeName === item.fee_name) {
                        const amount = parseFloat(item.amount) || 0;

                        $(this)
                            .val(amount)
                            .attr('data-original-amount', amount);
                    }
                });
            });

            if ($('#total_amount').length) {
                $('#total_amount').val(
                    parseFloat(response.total_amount || 0).toFixed(2)
                );
            }

            $('#due_date').val(response.due_date || '');
            $('#notes').val(response.notes || '');

            $('.fee-amount').prop('readonly', false);
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            resetAmounts();

            $('#due_date').val('');
            $('#notes').val('');
        }
    });
}


function resetVoucherFields() {
    resetAmounts();

    $('#due_date').val('');
    $('#notes').val('');

    if ($('#total_amount').length) {
        $('#total_amount').val('');
    }
}


function resetAmounts() {
    $('.fee-amount')
        .val(0)
        .attr('data-original-amount', 0)
        .prop('readonly', false);
}


function formatDate(date) {
    if (!date) {
        return '';
    }

    if (date.includes('T')) {
        return date.split('T')[0];
    }

    if (date.includes(' ')) {
        return date.split(' ')[0];
    }

    if (date.includes('/')) {
        const parts = date.split('/');

        if (parts.length === 3) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
    }

    return date;
}