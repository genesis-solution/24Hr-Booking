<?php

namespace MPHB\Notifier\Repositories;

use MPHB\Notifier\Entities\Notification;
use MPHB\Entities\WPPostData;
use MPHB\Repositories\AbstractPostRepository;
use MPHB\Notifier\PostTypes\NotificationCPT;

/**
 * @since 1.0
 */
class NotificationRepository extends AbstractPostRepository {

	protected $type = 'notification';


	/**
	 * @param string $type Optional. "any" by default.
	 * @param array  $atts Optional.
	 * @return \MPHB\Notifier\Entities\Notification[]
	 */
	public function findAllActive( $type = 'any', $atts = array() ) {

		$searchAtts = array_merge(
			array(
				'post_status' => Notification::NOTIFICATION_STATUS_ACTIVE,
				'order'       => 'ASC',
			),
			$atts
		);

		switch ( $type ) {

			case 'email':
				$searchAtts['meta_query'] = array(
					'relation' => 'AND',
					array(
						// ... With non-empty list of recipients
						'key'     => 'mphb_notification_recipients',
						'value'   => 'a:0:{}',
						'compare' => '!=',
					),
					array(
						'relation' => 'OR',
						array(
							// "email" is default type. If the field does not
							// exist - that's also email notification
							'key'     => 'mphb_notification_type',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'   => 'mphb_notification_type',
							'value' => 'email',
						),
					),
				);
				break;

			case 'any':
			case 'all':
				break;

			default:
				// Any other type
				$searchAtts['meta_query'] = array(
					array(
						'key'   => 'mphb_notification_type',
						'value' => $type,
					),
				);
				break;
		}

		return $this->findAll( $searchAtts );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $atts Optional.
	 * @return \MPHB\Notifier\Entities\Notification[]
	 */
	public function findAllByNewBooking( $booking, $atts = array() ) {

		MPHB()->translation()->switchLanguage( $booking->getLanguage() );

		$activeNotifications = $this->findAllActive( 'any', $atts );

		$suitableNotifications = array_filter(
			$activeNotifications,
			function( $notification ) use ( $booking ) {

				return \MPHB\Notifier\Utils\BookingUtils::isNotificationFitsToGoForNewBooking( $notification, $booking );
			}
		);

		return $suitableNotifications;
	}

	/**
	 * @param \WP_Post|int $post
	 * @return \MPHB\Notifier\Entities\Notification
	 */
	public function mapPostToEntity( $post ) {

		if ( is_a( $post, '\WP_Post' ) ) {

			$postId     = $post->ID;
			$postTitle  = $post->post_title;
			$postStatus = $post->post_status;

		} else {

			$postId     = $post;
			$postTitle  = get_the_title( $postId );
			$postStatus = get_post_status( $postId );
		}

		$originalId = apply_filters(
			'wpml_object_id',
			absint( $postId ),
			\MPHB\Notifier\Plugin::getInstance()->postTypes()->notification()->getPostType(),
			true,
			MPHB()->translation()->getDefaultLanguage()
		);

		$atts = array(
			'id'                                        => absint( $postId ),
			'originalId'                                => absint( $originalId ),
			'title'                                     => $postTitle,
			'status'                                    => $postStatus,
			'type'                                      => get_post_meta( $originalId, 'mphb_notification_type', true ),
			'is_sending_automatic'                      => 'manual' != get_post_meta( $originalId, 'mphb_notification_sending_mode', true ),
			'trigger'                                   => get_post_meta( $originalId, 'mphb_notification_trigger', true ),
			'is_disabled_for_reservation_after_trigger' => get_post_meta( $originalId, 'mphb_is_disabled_for_reservation_after_trigger', true ),
			'accommodation_type_ids'                    => get_post_meta( $originalId, 'mphb_notification_accommodation_type_ids', true ),
			'recipients'                                => get_post_meta( $originalId, 'mphb_notification_recipients', true ),
			'custom_emails'                             => get_post_meta( $originalId, 'mphb_notification_custom_emails', true ),
			'email_subject'                             => get_post_meta( $postId, 'mphb_notification_email_subject', true ),
			'email_header'                              => get_post_meta( $postId, 'mphb_notification_email_header', true ),
			'email_message'                             => get_post_meta( $postId, 'mphb_notification_email_message', true ),
		);

		if ( empty( $atts['type'] ) ) {
			$atts['type'] = 'email';
		}

		if ( ! is_array( $atts['trigger'] ) ) {
			unset( $atts['trigger'] );
		}

		if ( ! is_array( $atts['recipients'] ) ) {
			$atts['recipients'] = array();
		}

		if ( ! empty( $atts['custom_emails'] ) ) {

			$customEmails = explode( ',', $atts['custom_emails'] );
			$customEmails = array_map( 'trim', $customEmails );
			$customEmails = array_filter( $customEmails ); // Filter after trim()

			$atts['custom_emails'] = array_values( $customEmails );

		} else {

			$atts['custom_emails'] = array();
		}

		return new Notification( $atts );
	}

	/**
	 * @param \MPHB\Notifier\Entities\Notification $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$atts = array(
			'ID'          => $entity->getId(),
			'post_type'   => \MPHB\Notifier\Plugin::getInstance()->postTypes()->notification()->getPostType(),
			'post_title'  => $entity->getTitle(),
			'post_status' => $entity->getStatus(),
			'post_metas'  => array(
				'mphb_notification_type'                   => $entity->getType(),
				'mphb_notification_sending_mode'           => $entity->isSendingAutomatic() ? 'automatic' : 'manual',
				'mphb_notification_trigger'                => $entity->getTrigger(),
				'mphb_notification_accommodation_type_ids' => $entity->getAccommodationTypeIds(),
				'mphb_notification_recipients'             => $entity->getRecipients(),
				'mphb_notification_custom_emails'          => implode( ', ', $entity->getCustomEmails() ),
				'mphb_notification_email_subject'          => $entity->getSubject(),
				'mphb_notification_email_header'           => $entity->getHeader(),
				'mphb_notification_email_message'          => $entity->getMessage(),
			),
		);

		return new WPPostData( $atts );
	}
}
