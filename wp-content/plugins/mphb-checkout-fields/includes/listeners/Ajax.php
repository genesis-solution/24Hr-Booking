<?php

namespace MPHB\CheckoutFields\Listeners;

use MPHB\CheckoutFields\CheckoutFieldsHelper;

/**
 * @since 1.0
 */
class Ajax {

	public $nonceName = 'mphb_nonce';

	protected $actions = array(
		'mphb_cf_reorder_posts' => array(
			'method' => 'POST',
			'nopriv' => false,
		),
	);

	public function __construct() {
        
		$this->registerActions( $this->actions );
	}

	protected function registerActions( $actions ) {

		foreach ( $actions as $action => $args ) {
			$callback = array( $this, $action );

			add_action( "wp_ajax_{$action}", $callback );

			if ( $args['nopriv'] ) {
				add_action( "wp_ajax_nopriv_{$action}", $callback );
			}
		}
	}

	protected function checkNonce( $action ) {

		if ( ! $this->verifyNonce( $action ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Request does not pass security verification. Please refresh the page and try one more time.', 'mphb-checkout-fields' ),
				)
			);
		}
	}

	protected function verifyNonce( $action ) {

		$input = $this->retrieveInput( $action );
		$nonce = isset( $input[ $this->nonceName ] ) ? $input[ $this->nonceName ] : '';

		return wp_verify_nonce( $nonce, $action );
	}

	protected function retrieveInput( $action ) {
		$method = $this->actions[ $action ]['method'];

		switch ( strtolower( $method ) ) {
			case 'get':
				return $_GET;
			break;
			case 'post':
				return $_POST;
			break;
			default:
				return $_REQUEST;
			break;
		}
	}

	/**
	 * @global \wpdb $wpdb
	 */
	public function mphb_cf_reorder_posts() {

		global $wpdb;

		$this->checkNonce( __FUNCTION__ );

		// Parse input
		$input = $this->retrieveInput( __FUNCTION__ );

		$postId = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$nextId = isset( $input['next_post_id'] ) ? absint( $input['next_post_id'] ) : 0;

		if ( $postId == 0 ) {
			wp_send_json_error( array( 'message' => 'Post ID is not set.' ) ); // Unused, no translation required
		}

		// Get IDs of all fields
		$fields = CheckoutFieldsHelper::getCheckoutFieldsIds();

		if ( empty( $fields ) ) {
			wp_send_json_success();
		}

		// Reorder all fields
		$newOrder = 1;

		foreach ( $fields as $currentId ) {
			// Skip post with $postId, we'll set its order later, right before
			// the $nextId or in the end
			if ( $currentId == $postId ) {
				continue;
			}

			// If $nextId found, then use current order for $postId and then
			// increase order for the current item ($nextId)
			if ( $currentId == $nextId ) {
				$wpdb->update( $wpdb->posts, array( 'menu_order' => $newOrder ), array( 'ID' => $postId ), '%d' );
				$newOrder++;
			}

			// Set new order for current item
			$wpdb->update( $wpdb->posts, array( 'menu_order' => $newOrder ), array( 'ID' => $currentId ), '%d' );

			$newOrder++;
		}

		// If there is no $nextId then post with $postId is the last element,
		// don't forget to set new order for it too
		if ( $nextId == 0 ) {
			$wpdb->update( $wpdb->posts, array( 'menu_order' => $newOrder ), array( 'ID' => $postId ), '%d' );
		}

		wp_send_json_success();
	}
}
