<?php

class WPIM_Invoice_Manager {
   
    const INVOICE = 'invoice';

    public function __construct() {
        add_action('init', array($this, 'register_invoice_post_type'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('add_meta_boxes', array($this, 'add_invoice_meta_boxes'));
        add_action('save_post', array($this, 'save_invoice_meta'));
        add_filter('manage_edit-invoice_columns', array($this, 'set_invoice_columns'));
        add_action('manage_invoice_posts_custom_column', array($this, 'render_invoice_columns'), 10, 2);
        add_filter('manage_edit-invoice_sortable_columns', array($this, 'set_sortable_invoice_columns'));
        add_filter('bulk_actions-edit-invoice', array($this, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-edit-invoice', array($this, 'handle_bulk_actions'), 10, 3);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_invoice_js'));
        add_action('admin_post_wpim_download_invoice', array($this, 'handle_download_invoice_pdf'));
        add_action('admin_post_wpim_mark_invoice_paid', array($this, 'handle_mark_invoice_paid'));
        add_action('pre_get_posts', array($this, 'handle_columns_sorting'));
        add_action('wp_ajax_wpim_invoice_filter', array($this, 'wpim_invoice_filter'));
    }

    public function register_invoice_post_type() {
        $labels = array(
            'name' => 'Invoices',
            'singular_name' => 'Invoice',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Invoice',
            'edit_item' => 'Edit Invoice',
            'new_item' => 'New Invoice',
            'view_item' => 'View Invoice',
            'search_items' => 'Search Invoices',
            'not_found' => 'No invoices found',
            'not_found_in_trash' => 'No invoices found in Trash',
            'all_items' => 'All Invoices',
            'menu_name' => 'Invoices',
            'name_admin_bar' => 'Invoices',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'supports' => array('title'),
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-media-document',
            'show_ui' => true,
            'show_in_rest' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'rewrite' => ['slug' => 'invoice']
        );
        register_post_type('invoice', $args);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Invoice Generator',
            'Invoice Generator',
            'manage_options',
            'wpim-invoice-manager',
            array($this, 'admin_page_content'),
            'dashicons-media-document',
            6
        );
    }

    public function admin_page_content() {
        $add_new_url = admin_url('post-new.php?post_type=' . self::INVOICE);
        $all_invoices_url = admin_url('edit.php?post_type=' . self::INVOICE);
        echo '<div class="wrap"><h1>Invoice Manager</h1>';
        echo '<a href="' . esc_url($add_new_url) . '" class="page-title-action">Add Invoice</a>';
        echo '<a href="' . esc_url($all_invoices_url) . '" style="margin-left:15px;" class="page-title-action">All Invoices</a>';
        echo '<hr>';
        echo '<p>Welcome to the Invoice Manager plugin admin page.</p>';
        echo '<p>Here you can manage your invoices and plugin settings.</p>';
        echo '</div>';
    }

    public function add_invoice_meta_boxes() {
        add_meta_box(
            'wpim_invoice_details',
            'Invoice Details',
            array($this, 'invoice_details_callback'),
            'invoice',
            'normal',
            'high'
        );
    }

    // Add custom bulk actions
    public function add_bulk_actions($bulk_actions) {
        $bulk_actions['mark_as_draft'] = 'Draft';
        $bulk_actions['mark_as_sent'] = 'Sent';
        $bulk_actions['mark_as_paid'] = 'Paid';
        return $bulk_actions;
    }

    // Handle custom bulk actions
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if ($doaction === 'mark_as_paid' || $doaction === 'mark_as_sent') {
            $new_status = ($doaction === 'mark_as_paid') ? 'paid' : 'sent';
            $count = 0;
            foreach ($post_ids as $post_id) {
                if (get_post_type($post_id) === self::INVOICE) {
                    update_post_meta($post_id, 'status', $new_status);
                    if ($new_status === 'paid') {
                        update_post_meta($post_id, 'paid_date', current_time('mysql'));
                    }
                    $count++;
                }
            }
            $redirect_to = add_query_arg('bulk_marked_' . $new_status, $count, $redirect_to);
        }
        return $redirect_to;
    }

    // You can move your meta box callback and save logic here as methods
    public function invoice_details_callback($post) {
        $invoice_number = get_post_meta($post->ID, 'invoice_number', true);
        $client_name = get_post_meta($post->ID, 'client_name', true);
        $issue_date = get_post_meta($post->ID, 'issue_date', true);
        $due_date = get_post_meta($post->ID, 'due_date', true);
        $amount = get_post_meta($post->ID, 'amount', true);
        $status = get_post_meta($post->ID, 'status', true);
        $action = get_post_meta($post->ID, 'action', true);

        ?>
        <p>
            <label for="invoice_number">Invoice Number:</label><br>
            <input type="text" name="invoice_number" value="<?php echo esc_attr($invoice_number); ?>" readonly style="background:#eee;" />
        </p>
        <p>
            <label for="client_name">Client Name:</label><br>
            <input type="text" name="client_name" value="<?php echo esc_attr($client_name); ?>" />
        </p>
        <p>
            <label for="issue_date">Issue Date:</label><br>
            <input type="date" name="issue_date" value="<?php echo esc_attr($issue_date); ?>" />
        </p>
        <p>
            <label for="due_date">Due Date:</label><br>
            <input type="date" name="due_date" value="<?php echo esc_attr($due_date); ?>" />
        </p>
        <p>
            <label for="amount">Amount:</label><br>
            <input type="number" step="0.01" min="0" name="amount" value="<?php echo esc_attr(number_format((float)$amount, 2, '.', '')); ?>" pattern="^\\d+(\\.\\d{1,2})?$" title="Please enter a valid amount with up to two decimal places." />
        </p>
        <p>
            <label for="status">Status:</label><br>
            <select name="status">
                <option value="draft" <?php selected($status, 'draft'); ?>>Draft</option>
                <option value="sent" <?php selected($status, 'sent'); ?>>Sent</option>
                <option value="paid" <?php selected($status, 'paid'); ?>>Paid</option>
            </select>
        </p>
        <?php
    }

     // Add custom columns to invoice post type list
    public function set_invoice_columns($columns) {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'invoice_number' => 'Invoice Number',
            'client_name' => 'Client Name',
            'issue_date' => 'Issue Date',
            'due_date' => 'Due Date',
            'amount' => 'Amount',
            'status' => 'Status',
            'download_pdf' => 'Download PDF',
            'mark_as_paid' => 'Mark as Paid',
            'date' => 'Date',
        );
        return $columns;
    }

    // Populate custom columns
    public function render_invoice_columns($column, $post_id) {
        switch ($column) {
            case 'invoice_number':
                $invoice_number = get_post_meta($post_id, 'invoice_number', true);
                $edit_link = get_edit_post_link($post_id);
                echo '<a href="' . esc_url($edit_link) . '">' . esc_html($invoice_number) . '</a>';
                break;
            case 'client_name':
                echo esc_html(get_post_meta($post_id, 'client_name', true));
                break;
            case 'issue_date':
                echo esc_html(get_post_meta($post_id, 'issue_date', true));
                break;
            case 'due_date':
                echo esc_html(get_post_meta($post_id, 'due_date', true));
                break;
            case 'amount':
                echo esc_html(get_post_meta($post_id, 'amount', true));
                break;
            case 'status':
                echo esc_html(get_post_meta($post_id, 'status', true));
                break;
            case 'download_pdf':
                $invoice_number = get_post_meta($post_id, 'invoice_number', true);
                $pdf_path = plugin_dir_url(__FILE__) . 'invoices/invoice-' . $invoice_number . '.pdf';
                if (file_exists(plugin_dir_path(__FILE__) . 'invoices/invoice-' . $invoice_number . '.pdf')) {
                    echo '<a href="' . esc_url($pdf_path) . '" class="button" target="_blank">Download PDF</a>';
                } else {
                    $download_url = admin_url('admin-post.php?action=wpim_download_invoice&post_id=' . $post_id);
                    echo '<a href="' . esc_url($download_url) . '" class="button">Generate PDF</a>';
                }
                break;
            case 'mark_as_paid':
                $mark_paid_url = wp_nonce_url(admin_url('admin-post.php?action=wpim_mark_invoice_paid&post_id=' . $post_id), 'wpim_mark_invoice_paid_' . $post_id);
                $status = get_post_meta($post_id, 'status', true);
                if ($status !== 'paid') {
                    echo '<a href="' . esc_url($mark_paid_url) . '" class="button">Mark as Paid</a>';
                } else {
                    echo '<span style="color:green;font-weight:bold;">Paid</span>';
                }
                break;
        }
    }

     // Save invoice meta fields
    public function save_invoice_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (get_post_type($post_id) !== self::INVOICE) return;

        // Auto-generate and save invoice_number if not set
        $invoice_number = get_post_meta($post_id, 'invoice_number', true);
        if (empty($invoice_number)) {
            $date_str = date('Y-m-d');
            $invoice_number = 'INV-' . $date_str;
            // Uniqueness check
            $existing = get_posts(array(
                'post_type' => self::INVOICE,
                'meta_key' => 'invoice_number',
                'meta_value' => $invoice_number,
                'post_status' => 'any',
                'fields' => 'ids',
                'exclude' => array($post_id),
            ));
            if (!empty($existing)) {
                // If duplicate, add a counter suffix
                $counter = 2;
                do {
                    $new_invoice_number = $invoice_number . '-' . $counter;
                    $existing = get_posts(array(
                        'post_type' => self::INVOICE,
                        'meta_key' => 'invoice_number',
                        'meta_value' => $new_invoice_number,
                        'post_status' => 'any',
                        'fields' => 'ids',
                        'exclude' => array($post_id),
                    ));
                    if (empty($existing)) {
                        $invoice_number = $new_invoice_number;
                        break;
                    }
                    $counter++;
                } while ($counter < 1000);
            }
            update_post_meta($post_id, 'invoice_number', $invoice_number);
        }
        // Update post title to invoice_number, but avoid infinite loop
        if ($invoice_number && get_post($post_id)->post_title !== $invoice_number) {
            // Temporarily remove save_post action to prevent recursion
            remove_action('save_post', array($this, 'save_invoice_meta'));
            wp_update_post(array('ID' => $post_id, 'post_title' => $invoice_number));
            add_action('save_post', array($this, 'save_invoice_meta'));
        }
        // Save other fields
        $fields = array('client_name', 'issue_date', 'due_date', 'amount','Download PDF', 'status');
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'amount') {
                    $amount = $_POST['amount'];
                    // Validate float and two decimals
                    if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
                        wp_die('Amount must be a number with up to two decimal places.');
                    }
                    $amount = number_format((float)$amount, 2, '.', '');
                    update_post_meta($post_id, 'amount', $amount);
                } elseif ($field === 'client_name') {
                    $client_name = $_POST['client_name'];
                    // Allow only letters, spaces, and basic punctuation
                    $client_name = preg_replace("/[^ -\p{L}\p{N} .,'-]/u", '', $client_name);
                    $client_name = trim($client_name);
                    update_post_meta($post_id, 'client_name', $client_name);
                } else {
                    update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                }
            }
        }

        // Validate due date is later than issue date
        $issue_date = isset($_POST['issue_date']) ? $_POST['issue_date'] : get_post_meta($post_id, 'issue_date', true);
        $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : get_post_meta($post_id, 'due_date', true);
        if ($issue_date && $due_date) {
            $issue_ts = strtotime($issue_date);
            $due_ts = strtotime($due_date);
            if ($due_ts <= $issue_ts) {
                wp_die('Due Date must be later than Issue Date.');
            }
        }

        // Save PDF after invoice is saved
        $invoice_number = get_post_meta($post_id, 'invoice_number', true);
        if ($invoice_number) {
            $this->generate_invoice_pdf($post_id, true);
        }
    }
   

    // Make columns sortable
    public function set_sortable_invoice_columns($columns) {
        $columns['invoice_number'] = 'invoice_number';
        $columns['client_name'] = 'client_name';
        $columns['issue_date'] = 'issue_date';
        $columns['due_date'] = 'due_date';
        $columns['amount'] = 'amount';
        $columns['status'] = 'status';
        $columns['download_pdf'] = 'download_pdf';
        $columns['mark_as_paid'] = 'mark_as_paid';
        return $columns;
    }

    // Generate invoice PDF using dompdf
    public function generate_invoice_pdf($post_id) {
        require_once __DIR__ . '/vendor/autoload.php';
        $invoice_number = get_post_meta($post_id, 'invoice_number', true);
        $client_name = get_post_meta($post_id, 'client_name', true);
        $issue_date = get_post_meta($post_id, 'issue_date', true);
        $due_date = get_post_meta($post_id, 'due_date', true);
        $amount = get_post_meta($post_id, 'amount', true);
        $status = get_post_meta($post_id, 'status', true);

        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/invoice.php';
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $invoices_dir = plugin_dir_path(__FILE__) . 'invoices/';
        if (!is_dir($invoices_dir)) {
            mkdir($invoices_dir, 0755, true);
        }
        $pdf_file = $invoices_dir . 'invoice-' . $invoice_number . '.pdf';
        file_put_contents($pdf_file, $dompdf->output());

        // If not saving, stream to browser
        $args = func_get_args();
        if (isset($args[1]) && $args[1] === true) {
            return;
        }
        $dompdf->stream('invoice-' . $invoice_number . '.pdf', array('Attachment' => true));
        exit;
    }

    // Handle download PDF action
    public function handle_download_invoice_pdf() {
        if (!current_user_can('edit_posts') || empty($_GET['post_id'])) {
            wp_die('Unauthorized');
        }
        $post_id = intval($_GET['post_id']);
        $this->generate_invoice_pdf($post_id);
    }

     // Enqueue invoice.js from theme directory
    public function enqueue_invoice_js($hook) {
        global $post_type;
        if (($hook === 'post-new.php' || $hook === 'post.php') || $post_type === self::INVOICE) {
            $plugin_dir = plugin_dir_url(__FILE__);
            wp_enqueue_script('wpim-invoice-js', $plugin_dir . 'js/invoice.js', array('jquery'), null, true);
            wp_localize_script('wpim-invoice-js', 'wpimInvoiceFilter', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpim_invoice_filter_nonce')
            ));
        }
    }

    // Handle mark as paid action
    public function handle_mark_invoice_paid() {
        if (!isset($_GET['post_id']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpim_mark_invoice_paid_' . $_GET['post_id'])) {
            wp_die('Invalid request');
        }
        $post_id = intval($_GET['post_id']);
        if (!current_user_can('edit_post', $post_id)) {
            wp_die('Unauthorized');
        }
        update_post_meta($post_id, 'status', 'paid');
        update_post_meta($post_id, 'paid_date', current_time('mysql'));
        wp_redirect(admin_url('edit.php?post_type=invoice'));
        exit;
    }

    // Enable sorting for issue_date and amount columns
    function handle_columns_sorting($query){

        if (!is_admin() || !$query->is_main_query()) return;
        if ($query->get('post_type') !== WPIM_Invoice_Manager::INVOICE) return;
        
        $orderby = $query->get('orderby');
        if ($orderby === 'issue_date' || $orderby === 'amount') {
            $query->set('meta_key', $orderby);
            if ($orderby === 'amount') {
                $query->set('orderby', 'meta_value_num');
            } else {
                $query->set('orderby', 'meta_value');
            }
        }
    }

    // Add AJAX search/filter for invoices by number, client, or status
    function wpim_invoice_filter(){
        check_ajax_referer('wpim_invoice_filter_nonce', 'nonce');
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $args = array(
            'post_type' => WPIM_Invoice_Manager::INVOICE,
            'posts_per_page' => 20,
            'meta_query' => array('relation' => 'OR'),
        );
        if ($keyword !== '') {
            $args['meta_query'][] = array(
                'key' => 'invoice_number',
                'value' => $keyword,
                'compare' => 'LIKE'
            );
            $args['meta_query'][] = array(
                'key' => 'client_name',
                'value' => $keyword,
                'compare' => 'LIKE'
            );
            $args['meta_query'][] = array(
                'key' => 'status',
                'value' => $keyword,
                'compare' => 'LIKE'
            );
        }
        $query = new WP_Query($args);
        $results = array();
        foreach ($query->posts as $post) {
            $results[] = array(
                'id' => $post->ID,
                'invoice_number' => get_post_meta($post->ID, 'invoice_number', true),
                'client_name' => get_post_meta($post->ID, 'client_name', true),
                'issue_date' => get_post_meta($post->ID, 'issue_date', true),
                'due_date' => get_post_meta($post->ID, 'due_date', true),
                'amount' => get_post_meta($post->ID, 'amount', true),
                'status' => get_post_meta($post->ID, 'status', true),
                // Always use admin-post handler for download
                'pdf_url' => admin_url('admin-post.php?action=wpim_download_invoice&post_id=' . $post->ID),
                'date' => get_the_date('Y-m-d', $post),
            );
        }
        wp_send_json_success($results);
    }
}

