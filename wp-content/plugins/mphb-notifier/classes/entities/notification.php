<?php

namespace MPHB\Notifier\Entities;

/**
 * @since 1.0
 */
class Notification {

	const NOTIFICATION_STATUS_ACTIVE        = 'publish';
	const NOTIFICATION_STATUS_DISABLED      = 'draft';

	const NOTIFICATION_TYPE_EMAIL = 'email';

	private $id                                        = 0;
	private $originalId                                = 0;
	private $title                                     = '';
	private $status                                    = self::NOTIFICATION_STATUS_ACTIVE;
	private $type                                      = self::NOTIFICATION_TYPE_EMAIL;
	private $isSendingAutomatic                        = true;
	private $trigger                                   = array(
		'period'  => 1,
		'unit'    => 'day',
		'compare' => 'before',
		'field'   => 'check-in',
	);
	private $is_disabled_for_reservation_after_trigger = false;
	private $accommodationTypeIds                      = array();
	private $recipients                                = array();
	private $customEmails                              = array();
	private $subject                                   = '';
	private $header                                    = '';
	private $message                                   = '';


	public function __construct( $args = array() ) {

		$args = array_merge(
			array(
				'id'                     => 0,
				'originalId'             => 0,
				'title'                  => '',
				'status'                 => static::NOTIFICATION_STATUS_ACTIVE,
				'type'                   => static::NOTIFICATION_TYPE_EMAIL,
				'is_sending_automatic'   => true,
				'trigger'                => $this->trigger,
				'is_disabled_for_reservation_after_trigger' => false,
				'accommodation_type_ids' => array(),
				'recipients'             => array(),
				'custom_emails'          => array(),
				'email_subject'          => mphb_notifier()->settings()->getDefaultSubject(),
				'email_header'           => mphb_notifier()->settings()->getDefaultHeader(),
				'email_message'          => mphb_notifier()->settings()->getDefaultMessage(),
			),
			$args
		);

		$this->id                                        = $args['id'];
		$this->originalId                                = $args['originalId'];
		$this->title                                     = $args['title'];
		$this->status                                    = $args['status'];
		$this->type                                      = $args['type'];
		$this->isSendingAutomatic                        = $args['is_sending_automatic'];
		$this->trigger                                   = $args['trigger'];
		$this->is_disabled_for_reservation_after_trigger = $args['is_disabled_for_reservation_after_trigger'];
		$this->accommodationTypeIds                      = $args['accommodation_type_ids'];
		$this->recipients                                = $args['recipients'];
		$this->customEmails                              = $args['custom_emails'];
		$this->subject                                   = $args['email_subject'];
		$this->header                                    = $args['email_header'];
		$this->message                                   = $args['email_message'];
	}

	/**
	 * @return array [ status_id => status_label, ... ]
	 */
	public static function getAllNotificationStatueses() {

		return array(
			static::NOTIFICATION_STATUS_ACTIVE        => __( 'Active', 'mphb-notifier' ),
			static::NOTIFICATION_STATUS_DISABLED      => __( 'Disabled', 'mphb-notifier' ),
		);
	}

	/**
	 * @return array [ notification_type_id => notification_type_label, ... ]
	 */
	public static function getAllNotificationTypes() {

		return apply_filters(
			'mphb_notification_types',
			array(
				static::NOTIFICATION_TYPE_EMAIL => __( 'Email', 'mphb-notifier' ),
			)
		);
	}

	/**
	 * Some classes like repositories call getId() to get an ID of the entity.
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int id of notification on default language which has all notification settings meta data
	 */
	public function getOriginalId() {
		return $this->originalId;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getStatusLabel() {
		return static::getAllNotificationStatueses()[ $this->status ];
	}

	public function isActive() {
		return static::NOTIFICATION_STATUS_ACTIVE === $this->status;
	}

	public function isDisabled() {
		return static::NOTIFICATION_STATUS_DISABLED === $this->status;
	}

	public function getType() {
		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function isSendingAutomatic() {
		return $this->isSendingAutomatic;
	}

	/**
	 * @return array [ 'period' => 1, 'unit' => 'day', 'compare' => 'before', 'field' => 'check-in' ]
	 */
	public function getTrigger() {
		return $this->trigger;
	}

	public function isDisabledForReservationAfterTrigger() {
		return $this->is_disabled_for_reservation_after_trigger;
	}

	public function getAccommodationTypeIds() {
		return $this->accommodationTypeIds;
	}

	/**
	 * @return array can contains: admin, customer, custom
	 */
	public function getRecipients() {
		return $this->recipients;
	}

	/**
	 * @return bool
	 */
	public function hasRecipients() {
		return ! empty( $this->getRecipients() );
	}

	/**
	 * @return array of emails
	 */
	public function getCustomEmails() {
		return $this->customEmails;
	}

	/**
	 * @param \MPHB\Entities\Booking $booking Optional. Current booking.
	 * @return string[]
	 */
	public function getReceivers( $booking = null ) {

		if ( null == $booking ) {
			return array();
		}

		$emails = array();

		if ( in_array( 'admin', $this->getRecipients() ) &&
			( ! $booking->isImported() ||
			! mphb_notifier()->settings()->isDoNotSendImportedBookingsToAdmin() )
		) {

			$emails[] = mphb()->settings()->emails()->getHotelAdminEmail();
		}

		if ( in_array( 'customer', $this->getRecipients() ) &&
			( ! $booking->isImported() ||
			! mphb_notifier()->settings()->isDoNotSendImportedBookingsToCustomer() )
		) {

			$customerEmail = $booking->getCustomer()->getEmail();

			if ( ! empty( $customerEmail ) ) {

				$emails[] = $customerEmail;
			}
		}

		if ( in_array( 'custom', $this->getRecipients() ) &&
			( ! $booking->isImported() ||
			! mphb_notifier()->settings()->isDoNotSendImportedBookingsToCustomEmails() )
		) {

			$emails = array_merge( $emails, $this->getCustomEmails() );
		}

		return array_unique( $emails );
	}

	/**
	 * @return string
	 */
	public function getSlug() {

		// Decode any %## encoding in the title
		$slug = urldecode( $this->getTitle() );

		// Generate slug
		$slug = sanitize_title( $slug, (string) $this->getId() );

		// Decode any %## encoding again after function sanitize_title(), to
		// translate something like "%d0%be%d0%b4%d0%b8%d0%bd" into "один"
		$slug = urldecode( $slug );

		return $slug;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function getHeader() {
		return $this->header;
	}

	public function getMessage() {
		return $this->message;
	}
}
