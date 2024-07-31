<?php

namespace MPHB\Repositories;

use \MPHB\Entities;

class PaymentRepository extends AbstractPostRepository {

	protected $type = 'payment';

	/**
	 *
	 * @param type $post
	 * @return \MPHB\Entities\Payment
	 */
	public function mapPostToEntity( $post ) {

		if ( is_a( $post, '\WP_Post' ) ) {
			$id = $post->ID;
		} else {
			$id   = absint( $post );
			$post = get_post( $id );
		}

		$atts = array(
			'id'            => $id,
			'status'        => $post->post_status,
			'date'          => new \DateTime( $post->post_date ),
			'modifiedDate'  => new \DateTime( $post->post_modified ),
			'gatewayId'     => get_post_meta( $id, '_mphb_gateway', true ),
			'gatewayMode'   => get_post_meta( $id, '_mphb_gateway_mode', true ),
			'amount'        => (float) get_post_meta( $id, '_mphb_amount', true ),
			'currency'      => get_post_meta( $id, '_mphb_currency', true ),
			'transactionId' => get_post_meta( $id, '_mphb_transaction_id', true ),
			'bookingId'     => (int) get_post_meta( $id, '_mphb_booking_id', true ),
			'email'         => get_post_meta( $id, '_mphb_email', true ),
		);

		return new Entities\Payment( $atts );
	}

	/**
	 *
	 * @param Entities\Payment $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$postAtts = array(
			'ID'          => $entity->getId(),
			'post_metas'  => array(),
			'post_status' => $entity->getStatus(),
			'post_date'   => $entity->getDate()->format( 'Y-m-d H:i:s' ),
			'post_type'   => MPHB()->postTypes()->payment()->getPostType(),
		);

		$postAtts['post_metas'] = array(
			'_mphb_gateway'        => $entity->getGatewayId(),
			'_mphb_gateway_mode'   => $entity->getGatewayMode(),
			'_mphb_amount'         => $entity->getAmount(),
			'_mphb_currency'       => $entity->getCurrency(),
			'_mphb_transaction_id' => $entity->getTransactionId(),
			'_mphb_booking_id'     => $entity->getBookingId(),
			'_mphb_email'          => $entity->getEmail(),
		);
		return new Entities\WPPostData( $postAtts );
	}

	/**
	 *
	 * @param int $id
	 * @return Entities\Payment
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	/**
	 *
	 * @param array $atts
	 * @return Entities\Payment[]
	 */
	public function findAll( $atts = array() ) {
		return parent::findAll( $atts );
	}

}
