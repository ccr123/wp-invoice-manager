jQuery(document).ready(function($) {
    $('#post').on('submit', function(e) {
        var valid = true;
        var errorMessages = {};

        // Validate client_name
        var clientName = $('input[name="client_name"]').val();
        if (!clientName || clientName.trim() === '') {
            valid = false;
            errorMessages.client_name = 'Client Name is required.';
        }

        // Validate issue_date
        var issueDate = $('input[name="issue_date"]').val();
        if (!issueDate) {
            valid = false;
            errorMessages.issue_date = 'Issue Date is required.';
        }

        // Validate due_date
        var dueDate = $('input[name="due_date"]').val();
        if (!dueDate) {
            valid = false;
            errorMessages.due_date = 'Due Date is required.';
        }

        // Validate due_date is later than issue_date
        if (issueDate && dueDate) {
            var issueTs = new Date(issueDate).getTime();
            var dueTs = new Date(dueDate).getTime();
            if (dueTs <= issueTs) {
                valid = false;
                errorMessages.due_date = 'Due Date must be later than Issue Date.';
            }
        }

        // Validate amount
        var amount = $('input[name="amount"]').val();
        if (!amount || isNaN(amount) || parseFloat(amount) <= 0) {
            valid = false;
            errorMessages.amount = 'Amount must be a positive number.';
        }

        // Validate status
        var status = $('select[name="status"]').val();
        if (!status || ['draft','sent','paid'].indexOf(status) === -1) {
            valid = false;
            errorMessages.status = 'Status is required.';
        }

        // Remove previous errors
        $('.wpim-error').remove();

        // Show errors below each field
        $.each(errorMessages, function(field, msg) {
            var fieldElem = $('[name="' + field + '"]');
            if (field === 'status') {
                fieldElem = $('select[name="status"]');
            }
            fieldElem.after('<div class="wpim-error" style="color:red;">' + msg + '</div>');
        });

        if (!valid) {
            e.preventDefault();
        }
    });

    // AJAX invoice filter for admin list using .search-box and .search-results
   function wpimFilterInvoices() {
    var keyword = $('.search-box #post-search-input').val();
    var $tbody = $('#the-list');

    if (keyword.length < 1) {
        location.reload(); // Reset to full list if search is cleared
        return;
    }

    $tbody.html('<tr><td colspan="10"><em>Filtering invoices...</em></td></tr>');

    $.post(ajaxurl, {
        action: 'wpim_invoice_filter',
        keyword: keyword,
        nonce: wpimInvoiceFilter.nonce
    }, function (resp) {
        if (resp.success) {
            var html = '';
            if (resp.data.length) {
                $.each(resp.data, function (i, invoice) {
                    html += `
                        <tr id="post-${invoice.id}" class="iedit author-self level-0 post-${invoice.id} type-invoice status-${invoice.status} hentry">
                            <th scope="row" class="check-column">
                                <input id="cb-select-${invoice.id}" type="checkbox" name="post[]" value="${invoice.id}">
                                <label for="cb-select-${invoice.id}">
                                    <span class="screen-reader-text">Select ${invoice.invoice_number}</span>
                                </label>
                            </th>
                            <td class="invoice_number column-invoice_number"><a href="post.php?post=${invoice.id}&action=edit">${invoice.invoice_number}</a></td>
                            <td class="client_name column-client_name">${invoice.client_name}</td>
                            <td class="issue_date column-issue_date">${invoice.issue_date || '-'}</td>
                            <td class="due_date column-due_date">${invoice.due_date || '-'}</td>
                            <td class="amount column-amount">${invoice.amount}</td>
                            <td class="status column-status">${invoice.status}</td>
                            <td class="download_pdf column-download_pdf">
                                <a href="${invoice.pdf_url}" class="button" target="_blank">Download PDF</a>
                            </td>
                            <td class="mark_as_paid column-mark_as_paid">
                                ${invoice.status === 'paid'
                                    ? '<span style="color:green;font-weight:bold;">Paid</span>'
                                    : `<a href="admin-post.php?action=wpim_mark_invoice_paid&post_id=${invoice.id}&_wpnonce=${invoice._wpnonce}" class="button">Mark as Paid</a>`}
                            </td>
                            <td class="date column-date">${invoice.date || ''}</td>
                        </tr>`;
                });
            } else {
                html = '<tr><td colspan="10"><em>No invoices found.</em></td></tr>';
            }

            $tbody.html(html);
        } else {
            $tbody.html('<tr><td colspan="10"><em>Error filtering invoices.</em></td></tr>');
        }
    }).fail(function () {
        $tbody.html('<tr><td colspan="10"><em>Request failed.</em></td></tr>');
    });
}


    $(document).on('click', '#search-submit', function(e) {
        e.preventDefault();
        wpimFilterInvoices();
    });

    // Prevent form submit if inside a form
    $('.search-box').closest('form').on('submit', function(e){
        e.preventDefault();
        wpimFilterInvoices();
    });
});