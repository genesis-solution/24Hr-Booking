<?php

namespace MPHB\Addons\Invoice\Email;

class TagsProcessor {

	const INVOICE_TAG = 'pdf_invoice';

	protected $booking     = null;
	protected $attachments = array();

	public function __construct() {

		add_filter( 'mphb_email_payment_tags', array( $this, 'addTags' ), 999 );
		add_filter( 'mphb_email_replace_tag', array( $this, 'replaceTag' ), 999, 4 );
	}

	public function addTags( $tags ) {

		$tags[] = array(
			'name'        => self::INVOICE_TAG,
			'description' => __( 'PDF Invoice', 'mphb-invoices' ),
		);

		return $tags;
	}

	public function replaceTag( $replaceText, $tag, $booking, $payment ) {

		if ( $tag == self::INVOICE_TAG ) {

			$this->booking = $booking;
			if ( has_filter( 'wp_mail', array( $this, 'beforeMail' ) ) === false ) {
				add_filter( 'wp_mail', array( $this, 'beforeMail' ) );
			}
		}

		return $replaceText;
	}

	public function beforeMail( $args ) {

		remove_filter( 'wp_mail', array( $this, 'beforeMail' ) );

		if ( is_null( $this->booking ) ) {
			return $args;
		}

		$args ['attachments'] = mphbinvoice()->pdf()->addInvoicePdfAttachment( $args ['attachments'], $this->booking );
		$this->attachments    = $args ['attachments'];

		add_action( 'mphb_after_send_mail', array( $this, 'afterMailCleanup' ) );

		return $args;
	}

	public function afterMailCleanup() {

		remove_action( 'mphb_after_send_mail', array( $this, 'afterMailCleanup' ) );

		foreach ( $this->attachments as $filepath ) {
            
			if ( ( strpos( $filepath, 'invoice-' ) > -1 ) && ( strpos( $filepath, '.pdf' ) > -1 ) && file_exists( $filepath ) ) {
				unlink( $filepath );
			}
		}
	}

}
