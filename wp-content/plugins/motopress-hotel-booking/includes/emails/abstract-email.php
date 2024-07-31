<?php

namespace MPHB\Emails;

use \MPHB\Libraries\Emogrifier;
use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Entities\Booking;
use \MPHB\Entities\Payment;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractEmail {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $defaultSubject = '';

	/**
	 * @var string
	 */
	protected $defaultHeaderText = '';

	/**
	 * @var Booking
	 */
	protected $booking;

	/**
	 * @var Payment|null
	 * @since 3.8.6
	 */
	protected $payment = null;

	/**
	 * @var Templaters\EmailTemplaterr
	 */
	protected $templater;

	/**
	 * @var bool
	 * @since 3.7.2
	 */
	protected $isTestMode = false;

	/**
	 * @var \MPHB\UsersAndRoles\Customer
	 */
	protected $customer;

	/**
	 * @var array
	 */
	protected $user;

	/**
	 * @param array  $atts
	 * @param string $atts['id'] ID of Email.
	 * @param string $atts['label'] Label.
	 * @param string $atts['description'] Optional. Email description.
	 * @param string $atts['default_subject'] Optional. Default subject of email.
	 * @param string $atts['default_header_text'] Optional. Default text in header.
	 */
	public function __construct( $atts, \MPHB\Emails\Templaters\EmailTemplater $templater ) {

		$this->id        = $atts['id'];
		$this->templater = $templater;

		// TODO set description and label or remove it from constructor

		add_action( 'plugins_loaded', array( $this, 'initStrings' ) );
	}

	// TODO: remove this method and do not use it in \MPHB\Notifier\Emails\NotificationEmail
	public function initStrings() {
		$this->initDescription();
		$this->initLabel();
	}

	/**
	 * Send mail.
	 *
	 * @return bool
	 *
	 * @since 3.7.2 sends the message to the administrator email address in test mode.
	 * @since 3.8.6 added actions "mphb_before_send_mail" and "mphb_after_send_mail".
	 */
	public function send() {

		do_action( 'mphb_before_send_mail', $this );

		$isSended = MPHB()->emails()->getMailer()->send( $this->getReceiver(), $this->getSubject(), $this->getMessage() );

		do_action( 'mphb_after_send_mail', $this );

		return $isSended;
	}

	abstract protected function initLabel();

	abstract protected function initDescription();

	abstract protected function getReceiver();

	abstract protected function log( $isSended );

	abstract public function getDefaultSubject();

	abstract public function getDefaultMessageHeaderText();

	/**
	 * @param Booking $booking
	 * @param array   $atts Optional.
	 * @param Payment $atts['payment']
	 * @param bool    $atts['test_mode'] Trigger email but don't add the logs.
	 *        False by default.
	 * @return bool
	 *
	 * @since 3.7.2 added new attribute - "test_mode".
	 */
	public function trigger( $booking, $atts = array() ) {

		$this->isTestMode = isset( $atts['test_mode'] ) && $atts['test_mode'];

		// Setup booking and payment
		$this->setupBooking( $booking );
		$this->setupPayment( isset( $atts['payment'] ) ? $atts['payment'] : null );

		// Check if the email is disabled
		if ( ! $this->isTestMode && ( $this->isDisabled() || $this->isPrevented() ) ) {
			return false;
		}

		// Do we have any receiver?
		if ( ! $this->checkReceiver() ) {
			return false;
		}

		// Send an email
		$isSended = $this->send();

		if ( ! $this->isTestMode ) {
			$this->log( $isSended );
		}

		return $isSended;
	}

	/**
	 * @param \MPHB\UsersAndRoles\Customer $customer
	 */
	public function triggerCustomerRegistration( $customer, $userAtts, $atts = array(), $booking = null ) {

		$this->isTestMode = isset( $atts['test_mode'] ) && $atts['test_mode'];

		// Check if the email is disabled
		if ( ! $this->isTestMode && ( $this->isDisabled() || $this->isPrevented() ) ) {
			return false;
		}

		$this->setupCustomer( $customer, $userAtts );
		$this->setupBooking( $booking );

		// Do we have any receiver?
		if ( ! $this->checkReceiver() ) {
			return false;
		}

		// Send an email
		$isSended = $this->send();

		return $isSended;
	}

	/**
	 * @return bool
	 *
	 * @since 3.8.6
	 */
	protected function checkReceiver() {

		if ( empty( $this->getReceiver() ) ) {

			// Log error
			if ( ! $this->isTestMode ) {
				$this->booking->addLog( $this->receiverError(), $this->getAuthor() );
			}

			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	protected function getSubject() {

		$subjectTemplate = $this->getSubjectTemplate();

		$subject = $this->replaceTags( $subjectTemplate );

		return $subject;
	}

	/**
	 * @return string
	 */
	protected function getMessage() {

		// Generate the content first. Some tags may add actions to insert
		// styles into <head>
		$messageContent = $this->getMessageContent();

		$message  = $this->getMessageHeader();
		$message .= $messageContent;
		$message .= $this->getMessageFooter();

		$message = $this->applyStyles( $message );

		return $message;
	}

	/**
	 * Applies styles for mail html.
	 *
	 * @param string $html HTML of mail.
	 * @return string
	 */
	protected function applyStyles( $html ) {

		// make sure we only inline CSS for html emails
		ob_start();
		require MPHB()->getPluginPath( 'includes/emails/templates/email-styles.php' );
		$styles = ob_get_clean();

		// apply CSS styles inline for picky email clients
		$emogrifier = new Emogrifier\Emogrifier( $html, $styles );

		// Load polyfill for function mb_convert_encoding() if it not exists.
		// Emogrifier is bad in converting non-ASCII characters. See MB-1023
		if ( ! function_exists( 'mb_convert_encoding' ) ) {
			mphb_get_polyfill_for( 'mb_convert_encoding' );
		}

		$html = $emogrifier->emogrify();

		return $html;
	}

	/**
	 * @return string
	 */
	protected function getMessageHeader() {

		ob_start();
		$headerText = $this->getMessageHeaderText();
		$templateId = $this->id;
		require MPHB()->getPluginPath( 'includes/emails/templates/email-header.php' );
		$header = ob_get_contents();
		ob_end_clean();
		return $header;
	}

	/**
	 * @return string
	 */
	protected function getMessageContent() {

		$template = $this->getMessageTemplate();
		$content  = $this->replaceTags( $template );
		return $content;
	}

	/**
	 * @return string
	 */
	protected function getSubjectTemplate() {

		$template = $this->getOption( 'subject' );

		if ( empty( $template ) ) {
			$template = $this->getDefaultSubject();
		}

		return $template;
	}

	protected function getMessageHeaderTextTemplate() {

		$template = $this->getOption( 'header' );

		if ( empty( $template ) ) {
			$template = $this->getDefaultMessageHeaderText();
		}

		return $template;
	}

	/**
	 * @return string
	 */
	protected function getMessageTemplate() {

		$template = $this->getOption( 'content' );

		if ( empty( $template ) ) {
			$template = $this->getDefaultMessageTemplate();
		}

		return $template;
	}

	/**
	 * @return string
	 */
	protected function getMessageHeaderText() {

		$headerTemplate = $this->getMessageHeaderTextTemplate();

		$headerText = $this->replaceTags( $headerTemplate );

		return $headerText;
	}

	/**
	 * @return string
	 */
	protected function getMessageFooter() {

		ob_start();
		$footerText = $this->getMessageFooterText();
		require MPHB()->getPluginPath( 'includes/emails/templates/email-footer.php' );
		$footer = ob_get_contents();
		ob_end_clean();
		return $footer;
	}

	/**
	 * @return string
	 */
	protected function getMessageFooterText() {
		return MPHB()->settings()->emails()->getFooterText();
	}

	/**
	 * @param string $template
	 * @return string
	 */
	protected function replaceTags( $template ) {
		return $this->templater->replaceTags( $template );
	}

	/**
	 * @return string
	 */
	public function getDefaultMessageTemplate() {

		$templateName = str_replace( '_', '-', $this->id );
		ob_start();
		mphb_get_template_part( 'emails/' . $templateName );
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return bool
	 */
	public function isDisabled() {
		$disableOption = $this->getOption( 'disable', false );
		return \MPHB\Utils\ValidateUtils::validateBool( $disableOption );
	}

	/**
	 * @return bool
	 * @since 2.4.1
	 */
	public function isPrevented() {
		return (bool) apply_filters( "mphb_email_{$this->id}_prevent", false );
	}

	/**
	 * @note available after plugins_loaded
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @note available after plugins_loaded
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	public function generateSettingsFields( \MPHB\Admin\Tabs\SettingsTab $tab ) {

		$optionPrefix = 'mphb_email_' . $this->id;
		$group        = new Groups\SettingsGroup(
			$optionPrefix,
			$this->label,
			$tab->getOptionGroupName(),
			$this->description
		);

		$disableField = Fields\FieldFactory::create(
			$optionPrefix . '_disable',
			array(
				'type'        => 'checkbox',
				'inner_label' => __( 'Disable this email notification', 'motopress-hotel-booking' ),
			)
		);

		$subjectField = Fields\FieldFactory::create(
			$optionPrefix . '_subject',
			array(
				'type'         => 'text',
				'label'        => __( 'Subject', 'motopress-hotel-booking' ),
				'default'      => $this->getDefaultSubject(),
				'placeholder'  => $this->getDefaultSubject(),
				'size'         => 'large',
				'translatable' => true,
			)
		);

		$headerField = Fields\FieldFactory::create(
			$optionPrefix . '_header',
			array(
				'type'         => 'text',
				'label'        => __( 'Header', 'motopress-hotel-booking' ),
				'default'      => $this->getDefaultMessageHeaderText(),
				'placeholder'  => $this->getDefaultMessageHeaderText(),
				'size'         => 'large',
				'translatable' => true,
			)
		);

		$contentField = Fields\FieldFactory::create(
			$optionPrefix . '_content',
			array(
				'type'         => 'rich-editor',
				'label'        => __( 'Email Template', 'motopress-hotel-booking' ),
				'rows'         => 21,
				'default'      => $this->getDefaultMessageTemplate(),
				'translatable' => true,
				'description2' => $this->templater->getTagsDescription(),
			)
		);

		$group->addField( $disableField );
		$group->addField( $subjectField );
		$group->addField( $headerField );
		$group->addField( $contentField );

		$tab->addGroup( $group );
	}

	/**
	 * @param string $name
	 * @param string $default Optional.
	 * @return mixed
	 */
	protected function getOption( $name, $default = '' ) {

		$optionName = 'mphb_email_' . $this->id . '_' . $name;

		$optionValue = get_option( $optionName, $default );

		$optionValue = apply_filters( 'mphb_translate_string', $optionValue, $optionName );

		return $optionValue;
	}

	/**
	 * @param Booking $booking
	 */
	protected function setupBooking( $booking ) {

		$this->booking = $booking;
		$this->templater->setupBooking( $booking );
	}

	/**
	 * @param \MPHB\UsersAndRoles\Customer $customer
	 * @since 4.2.0
	 */
	protected function setupCustomer( $customer, $userAtts ) {

		$user = array();

		$user['user_id']    = absint( $userAtts['user_id'] );
		$user['user_pass']  = isset( $userAtts['user_pass'] ) ? sanitize_text_field( $userAtts['user_pass'] ) : '';
		$user['user_login'] = isset( $userAtts['user_login'] ) ? sanitize_text_field( $userAtts['user_login'] ) : '';

		$this->customer = $customer;
		$this->user     = $user;

		$this->templater->setupCustomer( $customer, $user );
	}

	/**
	 * @return Booking
	 * @since 3.8.6
	 */
	public function getBooking() {
		return $this->booking;
	}

	/**
	 * @param Payment|null $payment
	 * @since 3.8.6
	 */
	protected function setupPayment( $payment ) {
		$this->payment = $payment;
		$this->templater->setupPayment( $payment );
	}

	/**
	 * @return Payment|null
	 * @since 3.8.6
	 */
	public function getPayment() {
		return $this->payment;
	}

	/**
	 * @return bool
	 * @since 3.8.6
	 */
	public function isTestMode() {
		return $this->isTestMode;
	}

	/**
	 * @return string
	 * @since 3.8.6
	 */
	protected function receiverError() {
		return sprintf( __( '"%s" email will not be sent: there is no customer email in the booking.', 'motopress-hotel-booking' ), $this->label );
	}


	protected function getAuthor() {

		// Null in Booking::addLog() means "define automatically". Some addons,
		// like Request Payment, need to change it
		return null;
	}

	public function getDeprecatedNotices() {

		$notices = array();

		$deprecatedTags = $this->templater->getDeprecatedTags();
		if ( empty( $deprecatedTags ) ) {
			$notices;
		}

		$deprecatedTagsStr      = '%' . join( '%|%', $deprecatedTags ) . '%';
		$hasDeprecatedTagsRegEx = '/' . $deprecatedTagsStr . '/';

		if ( preg_match( $hasDeprecatedTagsRegEx, $this->getMessageHeaderTextTemplate() ) ) {
			$notices[] = sprintf( __( 'Deprecated tags in header of %s', 'motopress-hotel-booking' ), $this->label );
		}
		if ( preg_match( $hasDeprecatedTagsRegEx, $this->getSubjectTemplate() ) ) {
			$notices[] = sprintf( __( 'Deprecated tags in subject of %s', 'motopress-hotel-booking' ), $this->label );
		}
		if ( preg_match( $hasDeprecatedTagsRegEx, $this->getMessageTemplate() ) ) {
			$notices[] = sprintf( __( 'Deprecated tags in template of %s', 'motopress-hotel-booking' ), $this->label );
		}

		return $notices;
	}
}
