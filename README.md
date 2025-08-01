# wp-invoice-manager

## Since there was time limit I had to use Local WP for faster wordpress installation

## How to Install WordPress Locally (using Local WP)

1. Download and install [Local](https://localwp.com/) (by WP Engine) for your OS.
2. Open Local and click "+ New Site".
3. Enter a site name (e.g., `wp-invoices`).
4. Choose "Preferred" environment (recommended) and continue.
5. Set up WordPress username, password, and email.
6. Click "Add Site" and wait for setup to complete.
7. Once ready, click "Open Site" to view your local WordPress site.
8. To install this plugin, copy the `wp-invoice-manager` folder into `app/public/wp-content/plugins/` of your Local site directory.
9. Go to WP Admin → Plugins and activate "WP Invoice Manager".

You now have a fresh local WordPress install with the invoice manager plugin ready to use.

## Only if there is error for pdf generation
## How to Install dompdf (PDF generator)

1. Open a terminal/command prompt.
2. Navigate to your plugin directory:
   ```
   cd path/to/wp-invoice-manager
   ```
3. Run the following command to install dompdf using Composer:
   ```
   composer require dompdf/dompdf
   ```
4. After installation, `vendor/` and `vendor/autoload.php` will be created in your plugin folder.
5. The plugin will automatically use dompdf for PDF generation.

## Features & Steps

- Registers a custom post type `invoice` for managing invoices in WordPress.
- Added admin menu
- Adds meta fields: invoice_number (auto-generated, unique), client_name, issue_date, due_date, amount (float, 2 decimals), status.
- Validates all fields (including date logic and amount format) before saving.
- Displays custom columns in the admin list: invoice_number, client_name, issue_date, due_date, amount, status, download PDF, mark as paid.
- Enables sorting on issue_date and amount columns.
- Allows bulk actions: mark as paid/sent (updates status and paid_date).
- Adds AJAX search for invoices by invoice_number (requires search input and results container in admin UI).
- Provides a one-click "Mark as Paid" button (AJAX or standard) for each invoice.
- Generates and saves PDF for each invoice using dompdf.
- Sanitizes all user input for security.
- Easy search functionality without page reload.
See `invoice-generator.php` for implementation details.


## Use of plugin Classic editor for better UI

## screenshot walkthrough that shows the main flows in action.
- Simple invoice generator plugin with easy triggers and buttons.

- Screenshot 1 shows the main invoice listing page where you can create edit or delete the invoices. Clicking on download button downloads/opens the invoice pdf in new tab.
-Mark as paid call to action button marks the invoice as paid and updates the date of update in post meta.
-Bulk Action buttons to edit / delete or change the status to draft/ sent or paid.


- Screenshot 2 shows the entry page for adding invoice, invoice number is disabled so there won't be any duplicates, Page title will be updated as invoice number.