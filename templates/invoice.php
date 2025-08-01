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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }

        .invoice-container {
            background: #fff;
            padding: 40px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }

        .invoice-title {
            font-size: 2rem;
            font-weight: bold;
            color: #00508C;
        }

        .invoice-date {
            font-size: 0.95rem;
            color: #555;
        }

        .invoice-details {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .invoice-details th,
        .invoice-details td {
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
        }

        .invoice-details th {
            background-color: #f2f2f2;
            font-weight: 600;
        }

        .invoice-message {
            margin-top: 30px;
            font-size: 1.1rem;
            background: #f7f9fc;
            padding: 16px 20px;
            border-left: 4px solid #00508C;
            border-radius: 4px;
        }

        .status-paid {
            color: green;
            font-weight: bold;
        }

        .status-due {
            color: red;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="invoice-title">Invoice #<?php echo esc_html($invoice_number); ?></div>
            <div class="invoice-date">Created on: <?php echo esc_html($issue_date); ?></div>
        </div>

        <table class="invoice-details">
            <tr>
                <th>Client Name</th>
                <td><?php echo esc_html($client_name); ?></td>
            </tr>
            <tr>
                <th>Amount</th>
                <td><?php echo esc_html($amount); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td class="status-<?php echo esc_attr(strtolower($status)); ?>">
                    <?php echo esc_html(ucfirst($status)); ?>
                </td>
            </tr>
            <tr>
                <th>Initiated At</th>
                <td><?php echo esc_html($issue_date); ?></td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td><?php echo esc_html($due_date); ?></td>
            </tr>
        </table>

        <div class="invoice-message">
            Dear <strong><?php echo esc_html($client_name); ?></strong>, your invoice of <strong><?php echo esc_html($amount); ?></strong> is due on <strong><?php echo esc_html($due_date); ?></strong>. This was initiated on <strong><?php echo esc_html($issue_date); ?></strong>.
        </div>
    </div>
</body>
</html>
