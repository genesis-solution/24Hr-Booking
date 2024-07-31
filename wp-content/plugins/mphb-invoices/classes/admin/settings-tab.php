<?php

namespace MPHB\Addons\Invoice\Admin;

use MPHB\Admin\Fields\FieldFactory;
use MPHB\Admin\Groups\SettingsGroup;
use MPHB\Admin\Tabs\SettingsSubTab as SettingsSubtab;

class SettingsTab
{
    public function __construct()
    {
        add_action('mphb_generate_extension_settings', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function enqueueScripts()
    {
        wp_enqueue_media();
    }

    /**
     * @param \MPHB\Admin\Tabs\SettingsTab $tab
     */
    public function registerSettings($tab)
    {
        $subtab = new SettingsSubtab('invoice', esc_html__('Invoices', 'mphb-invoices'),
            $tab->getPageName(), $tab->getName());

        $mainGroup = new SettingsGroup('mphb_invoice_general', '', $subtab->getOptionGroupName());

        $mainFields = [
			'invoice_instructions' => FieldFactory::create('mphb_invoice_instructions', [
				'type'        => 'placeholder',
				'label'       => esc_html__('Send Invoice in Email', 'mphb-invoices'),
				'default'     => esc_html__('Copy and paste the %pdf_invoice% tag to the Approved booking email template to send invoice as an attachment.', 'mphb-invoices'),
			]),
            'invoice_title' => FieldFactory::create('mphb_invoice_title', [
                'type'           => 'text',
                'label'          => esc_html__('Invoice Title', 'mphb-invoices'),
                'description'    => '',
                'default'        => ''
            ]),
            'company_logo' => FieldFactory::create('mphb_invoice_company_logo', [
                'type'           => 'media',
                'label'          => esc_html__('Company Logo', 'mphb-invoices'),
                'thumbnail_size' => 'full',
                'single'          => true,
                'description'    => '',
                'default'        => ''
            ]),
            'company_name' => FieldFactory::create('mphb_invoice_company_name', [
                'type'           => 'text',
                'label'          => esc_html__('Company Name', 'mphb-invoices'),
                'description'    => esc_html__('Company name shown on invoice.', 'mphb-invoices'),
                'default'        => ''
            ]),
            'company_information' => FieldFactory::create('mphb_invoice_company_information', [
                'type'           => 'textarea',
                'label'          => esc_html__('Company Information', 'mphb-invoices'),
                'description'    => esc_html__('Company information shown on invoice.', 'mphb-invoices'),
                'default'        => '',
                'rows'           => 5
            ]),
            'bottom_information' => FieldFactory::create('mphb_invoice_bottom_information', [
                'type'           => 'textarea',
                'label'          => esc_html__('Additional Information', 'mphb-invoices'),
                'description'    => esc_html__('The text entered in this box will appear at the bottom of the invoice.', 'mphb-invoices'),
                'default'        => '',
                'rows'           => 5,
            ]),
            'add_link_to_confirmation' => FieldFactory::create('mphb_invoice_add_link_to_confirmation', [
                'type'           => 'checkbox',
                'label'          => esc_html__('Invoice Link on Confirmation Page', 'mphb-invoices'),
                'inner_label'          => esc_html__('Add a link to a PDF invoice to the booking confirmation page on your site.', 'mphb-invoices'),
                'description'    => '',
                'default'        => false,
            ]),
            'choose_font' => FieldFactory::create( 'mphb_invoice_choose_font', [
                'type'           => 'select',
                'list'           => array(
                    'open_sans' => esc_html__('Open Sans', 'mphb-invoice'),
                    'courier' => esc_html__('Courier', 'mphb-invoice'),
                    'dejavu' => esc_html__('DejaVu', 'mphb-invoice'),
                    'helvetica' => esc_html__('Helvetica', 'mphb-invoice'),
                    'ipam' => esc_html__('Ipam (Japanese)', 'mphb-invoice'),
                    'times' => esc_html__('Times', 'mphb-invoice'),
                    'ibmplexsansthai' => esc_html__('IBM Plex Sans (Thai)', 'mphb-invoice')
                ),
                'label' => esc_html__('Font', 'mphb-invoice'),
                'description' => esc_html__('Font that will be used for the PDF template.', 'mphb-invoice')
            ])
        ];


        $mainGroup->addFields($mainFields);
        $subtab->addGroup($mainGroup);

        // Add License group
        if (mphb_invoice_use_edd_license()) {
            $licenseGroup = new LicenseSettingsGroup('mphb_mc_license', esc_html__('License', 'mphb-invoices'), $subtab->getOptionGroupName());
            $subtab->addGroup($licenseGroup);
        }

        $tab->addSubTab($subtab);
    }
}
