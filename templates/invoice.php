<?php
/**
 * Invoice Template
 * Usage: include this file to display a single invoice
 * Variables expected: $client_name, $issue_date, $due_date, $amount, $status, $invoice_number
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo esc_html($invoice_number); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; }
        .invoice-title { text-align: center; font-size: 2em; font-weight: bold; margin-top: 40px; }
        .invoice-details { margin-top: 30px; }
        .invoice-details td { padding: 8px 16px; }
        .invoice-message { margin-top: 40px; font-size: 1.2em; }
        .invoice-created {float:right; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div></div>
        <div class="invoice-title">Invoice <?php echo esc_html($invoice_number); ?></div>
        <div class="invoice-created">Date: <?php echo esc_html($issue_date); ?></div>
    </div>
    <table class="invoice-details">
        <tr>
            <td><strong>Client Name:</strong></td>
            <td><?php echo esc_html($client_name); ?></td>
        </tr>
        <tr>
            <td><strong>Amount:</strong></td>
            <td><?php echo esc_html($amount); ?></td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><?php echo esc_html(ucfirst($status)); ?></td>
        </tr>
        <tr>
            <td><strong>Initiated At:</strong></td>
            <td><?php echo esc_html($issue_date); ?></td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong></td>
            <td><?php echo esc_html($due_date); ?></td>
        </tr>
    </table>
    <div class="invoice-message">
        Dear <?php echo esc_html($client_name); ?>, your invoice is due at <?php echo esc_html($due_date); ?>, initiated at <?php echo esc_html($issue_date); ?>.
    </div>
</body>
</html>
