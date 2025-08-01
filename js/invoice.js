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
});
