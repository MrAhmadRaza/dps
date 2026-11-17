$(document).ready(function() {
    const token = $('meta[name="csrf-token"]').attr('content');
    const academicSessionId = window.selectedAcademicSessionId || $('#exit_academic_session_id').val();
    const selectedSessionItemId = window.selectedSessionItemId || null;
    // Edit page par existing session load
    if (academicSessionId) {
        loadSessionItems(window.classSectionUrl, token, academicSessionId, selectedSessionItemId);
    }
    // Academic session change
    $('#exit_academic_session_id').on('change', function() {
        const academicSessionId = $(this).val();
        $('#session_item_id').html('<option value="">Select</option>');
        $('#fee-details-section').hide();
        $('#fee-details-container').html('');
        $('#total_amount').val('');
        if (!academicSessionId) {
            return;
        }
        loadSessionItems(window.classSectionUrl, token, academicSessionId, null);
    });
    // Class / Section change
    $('#session_item_id').on('change', function() {
        const sessionItemId = $(this).val();
        const academicSessionId = $('#exit_academic_session_id').val();
        $('#fee-details-container').html('');
        $('#fee-details-section').hide();
        $('#total_amount').val('');
        if (!academicSessionId || !sessionItemId) {
            return;
        }
        loadVoucherAmounts(academicSessionId, sessionItemId, token);
    });
});
/*
|--------------------------------------------------------------------------
| Load Session Items
|--------------------------------------------------------------------------
*/
function loadSessionItems(routeUrl, token, academicSessionId, selectedSessionItemId = null) {
    $.ajax({
        url: routeUrl,
        method: 'POST',
        dataType: 'json',
        data: {
            _token: token,
            academic_session_id: academicSessionId
        },
        success: function(response) {
            $('#session_item_id').html('<option value="">Select</option>');
            if (response.status_code !== 200) {
                return;
            }
            $.each(response.session_items, function(index, item) {
                let selected = '';
                // Edit page selected class
                if (selectedSessionItemId && String(item.id) === String(selectedSessionItemId)) {
                    selected = 'selected';
                }
                // Agar sirf aik class hai
                if (!selectedSessionItemId && response.session_items.length === 1) {
                    selected = 'selected';
                }
                $('#session_item_id').append('<option value="' + item.id + '" ' + selected + '>' + 'Class ' + item.class + ' - Section ' + item.section + '</option>');
            });
            const sessionItemId = $('#session_item_id').val();
            // Automatically load fees
            if (sessionItemId) {
                loadVoucherAmounts(academicSessionId, sessionItemId, token);
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            $('#session_item_id').html('<option value="">Select</option>');
        }
    });
}
/*
|--------------------------------------------------------------------------
| Load Voucher Amounts
|--------------------------------------------------------------------------
*/
function loadVoucherAmounts(academicSessionId, sessionItemId, token) {
    $.ajax({
        url: window.voucherAmountsUrl,
        method: 'POST',
        dataType: 'json',
        data: {
            _token: token,
            academic_session_id: academicSessionId,
            session_item_id: sessionItemId
        },
        success: function(response) {
            $('#fee-details-container').html('');
            $('#total_amount').val('');
            if (response.status_code !== 200) {
                $('#fee-details-section').hide();
                return;
            }
            $('#fee-details-section').show();
            /*
            |--------------------------------------------------------------------------
            | Voucher Exists
            |--------------------------------------------------------------------------
            */
            const hasVoucher = response.voucher_exists === true;
            $('#fee-details-section').attr('data-voucher-exists', hasVoucher ? '1' : '0');
            /*
            |--------------------------------------------------------------------------
            | Original Total
            |--------------------------------------------------------------------------
            */
            const totalAmount = parseFloat(response.total_amount) || 0;
            $('#total_amount').val(totalAmount.toFixed(2)).attr('data-original-total', totalAmount);
            /*
            |--------------------------------------------------------------------------
            | Fee Items
            |--------------------------------------------------------------------------
            */
            $.each(response.voucher_items, function(index, item) {
                const amount = parseFloat(item.amount) || 0;
                const readonly = hasVoucher ? 'readonly' : '';
                $('#fee-details-container').append(`

                        <div class="col-md-4 mb-3">

                            <label class="form-label fw-bold">
                                ${item.fee_name}
                            </label>


                            <input
                                type="hidden"
                                name="fee_names[]"
                                value="${item.fee_name}"
                            >


                            <input
                                type="number"
                                name="amounts[]"
                                class="form-control fee-amount"
                                value="${amount}"
                                min="0"
                                step="0.01"
                                data-fee-name="${item.fee_name}"
                                data-original-amount="${amount}"
                                ${readonly}
                            >

                        </div>

                    `);
                    
            });
            /*
            |--------------------------------------------------------------------------
            | Due Date
            |--------------------------------------------------------------------------
            */
          
            const originalDueDate = response.due_date || '';

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            const originalNotes = response.notes || '';

            $('#fee-details-container').append(`

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Due Date <span class="text-danger">*</span>
                    </label>

                    <input
                        type="date"
                        name="due_date"
                        id="due_date"
                        class="form-control"
                        value="${originalDueDate}"
                        data-original-value="${originalDueDate}"
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="2"
                        class="form-control"
                        data-original-value="${originalNotes}"
                    >${originalNotes}</textarea>

                </div>
            `);

            // AJAX fields create hone ke baad current discount/voucher state apply karo
            if (typeof applyDiscountState === 'function') {
                applyDiscountState();
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            $('#fee-details-section').hide();
            $('#fee-details-container').html('');
            $('#total_amount').val('');
            $('#total_amount').removeAttr('data-original-total');
        }
    });
}
/*
|--------------------------------------------------------------------------
| Fee Amount Total
|--------------------------------------------------------------------------
*/
$(document).on('input', '.fee-amount', function() {
    const hasVoucher = $('#fee-details-section').attr('data-voucher-exists') === '1';
    if (hasVoucher) {
        return;
    }
    let total = 0;
    $('.fee-amount').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#total_amount').val(total.toFixed(2));
});