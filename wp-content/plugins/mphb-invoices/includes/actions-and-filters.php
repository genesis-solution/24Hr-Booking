<?php

if (!defined('ABSPATH')) {
    exit;
}


add_filter( 'post_row_actions', 'mphb_invoice_add_print_button', 10, 2 );

new MPHB\Addons\Invoice\MetaBoxes\InvoiceMetaBox('print_invoice', esc_html__('Invoice', 'mphb-invoices'),
    MPHB()->postTypes()->booking()->getPostType(), 'side');

add_action( 'admin_action_mphb-invoice', 'mphb_invoice_action_printpdf' );

add_action( 'init', 'mphb_invoice_analyze_request', 999 );

add_action( 'mphb_sc_booking_confirmation_bottom', 'mphb_invoice_add_secure_pdf_link');
