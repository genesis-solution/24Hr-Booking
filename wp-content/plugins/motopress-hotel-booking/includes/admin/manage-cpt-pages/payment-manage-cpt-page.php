<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\Entities;
use \MPHB\PostTypes\PaymentCPT;

class PaymentManageCPTPage extends ManageCPTPage {

	public function __construct( $postType, $atts = array() ) {
		parent::__construct( $postType, $atts );
		add_filter( 'post_row_actions', array( $this, 'filterRowActions' ) );
		add_filter( 'bulk_actions-edit-' . $this->postType, array( $this, 'filterBulkActions' ) );
		add_filter( 'request', array( $this, 'filterCustomOrderBy' ) );

		if ( is_admin() ) {
			add_filter( 'posts_join', array( $this, 'extendSearchPostsJoin' ), 10, 2 );
			add_filter( 'posts_search', array( $this, 'extendPostsSearch' ), 10, 2 );
			add_filter( 'posts_search_orderby', array( $this, 'extendPostsSearchOrderBy' ), 10, 2 );
			add_filter( 'posts_distinct', array( $this, 'searchDistinct' ) );
		}
	}

	public function filterColumns( $columns ) {
		$customColumns = array(
			'id'                  => __( 'ID', 'motopress-hotel-booking' ),
			'mphb_customer'       => __( 'Customer', 'motopress-hotel-booking' ),
			'mphb_status'         => __( 'Status', 'motopress-hotel-booking' ),
			'mphb_amount'         => __( 'Amount', 'motopress-hotel-booking' ),
			'mphb_booking_id'     => __( 'Booking', 'motopress-hotel-booking' ),
			'mphb_gateway'        => __( 'Gateway', 'motopress-hotel-booking' ),
			'mphb_transaction_id' => __( 'Transaction ID', 'motopress-hotel-booking' ),
			'mphb_dates'          => __( 'Date', 'motopress-hotel-booking' ),
		);
		$offset        = array_search( 'date', array_keys( $columns ) ); // Set custom columns position before "DATE" column
		$columns       = array_slice( $columns, 0, $offset, true ) + $customColumns + array_slice( $columns, $offset, count( $columns ) - 1, true );

		unset( $columns['title'] );
		unset( $columns['date'] );

		return $columns;
	}

	public function filterSortableColumns( $columns ) {

		$columns['id']          = 'ID';
		$columns['mphb_amount'] = 'mphb_amount';

		return $columns;
	}

	public function renderColumns( $column, $postId ) {

		$payment = MPHB()->getPaymentRepository()->findById( $postId );

		switch ( $column ) {

			case 'id':
				printf( '<a href="%s"><strong>' . esc_html( '#%s' ) . '</strong></a>', esc_url( get_edit_post_link( $postId ) ), esc_html( $postId ) );
				break;

			case 'mphb_customer':
				$email = sanitize_email( $payment->getEmail() );
				if ( ! empty( $email ) ) {
					printf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( $email ), esc_html( $email ) );
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo static::EMPTY_VALUE_PLACEHOLDER;
				}
				break;

			case 'mphb_status':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?><span class="column-status-<?php echo esc_attr( $payment->getStatus() ); ?>"><?php echo mphb_get_status_label( $payment->getStatus() ); ?></span>
				<?php
				if ( $payment->getStatus() === PaymentCPT\Statuses::STATUS_PENDING ) {
					$expireTime = $payment->retrieveExpiration();
					if ( $expireTime ) {
						$currentTime = time();
						echo '<br/>';
						echo '<small>';
						if ( $expireTime > $currentTime ) {
							echo esc_html( sprintf( __( 'Expire %s', 'motopress-hotel-booking' ), human_time_diff( $currentTime, $expireTime ) ) );
						} else {
							esc_html_e( 'Expired', 'motopress-hotel-booking' );
						}
						echo '</small>';
					}
				}
				break;

			case 'mphb_amount':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo mphb_format_price(
					$payment->getAmount(),
					array(
						'currency_symbol' => MPHB()->settings()->currency()->getBundle()->getSymbol( $payment->getCurrency() ),
					)
				);
				break;

			case 'mphb_booking_id':
				$booking = $payment->getBookingId() ? MPHB()->getBookingRepository()->findById( $payment->getBookingId() ) : null;
				if ( $booking ) {
					printf( '<a href="%s">' . esc_html__( 'Booking #%s', 'motopress-hotel-booking' ) . '</a>', esc_url( get_edit_post_link( $booking->getId() ) ), esc_html( $booking->getId() ) );
				}
				break;

			case 'mphb_gateway':
				$gateway = MPHB()->gatewayManager()->getGateway( $payment->getGatewayId() );

				if ( $gateway ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $gateway->getAdminTitle();
					$getGatewayMode = $payment->getGatewayMode();
					if ( $getGatewayMode == 'sandbox' ) {
						echo '<br><span style="color:#999">' . esc_html( ucfirst( $getGatewayMode ) ) . '</span>';
					}
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<span aria-hidden="true">' . static::EMPTY_VALUE_PLACEHOLDER . '</span>';
				}
				break;

			case 'mphb_transaction_id':
				$transactionId = $payment->getTransactionId();
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo empty( $transactionId ) ? '<span aria-hidden="true">' . static::EMPTY_VALUE_PLACEHOLDER . '</span>' : esc_html( $transactionId );
				break;

			case 'mphb_dates':
				?>
				<abbr title="<?php echo esc_attr( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP(), $payment->getDate()->getTimestamp() ) ); ?>">
					<?php echo esc_html( date_i18n( 'Y/m/d', $payment->getDate()->getTimestamp() ) ); ?>
				</abbr>
				<?php
				break;
		}
	}

	public function filterCustomOrderBy( $vars ) {
		if ( $this->isCurrentPage() && isset( $vars['orderby'] ) ) {
			switch ( $vars['orderby'] ) {
				case 'mphb_amount':
					$vars = array_merge(
						$vars,
						array(
							'meta_key'  => '_mphb_amount',
							'orderby'   => 'meta_value',
							'meta_type' => 'NUMERIC',
						)
					);
					break;
			}
		}
		return $vars;
	}

	public function filterRowActions( $actions ) {
		// Prevent Quick Edit
		if ( $this->isCurrentPage() ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				unset( $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}

	public function filterBulkActions( $bulkActions ) {
		if ( isset( $bulkActions['edit'] ) ) {
			unset( $bulkActions['edit'] );
		}
		return $bulkActions;
	}

	/**
	 *
	 * @global \WPDB $wpdb
	 * @param string    $where
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function extendPostsSearch( $where, $wp_query ) {
		global $wpdb;

		if ( $this->isCurrentPage() && ! empty( $wp_query->query['s'] ) ) {

			$search = trim( $wp_query->query['s'] );

			$customWhere = '';

			if ( is_email( $search ) ) {
				$joinCount = $wp_query->get( '_mphb_join_meta', 0 ) + 1;
				$wp_query->set( '_mphb_join_meta', $joinCount );

				$customWhere = $wpdb->prepare( "( mphb_postmeta_{$joinCount}.meta_key = %s AND CAST( mphb_postmeta_{$joinCount}.meta_value as CHAR ) = %s )", '_mphb_email', $search );
			} elseif ( is_numeric( $search ) ) {
				if ( get_post_type( $search ) === MPHB()->postTypes()->booking()->getPostType() ) {
					$joinCount = $wp_query->get( '_mphb_join_meta', 0 ) + 1;
					$wp_query->set( '_mphb_join_meta', $joinCount );

					$customWhere = $wpdb->prepare( "( mphb_postmeta_{$joinCount}.meta_key = %s AND CAST( mphb_postmeta_{$joinCount}.meta_value as CHAR ) = %s )", '_mphb_booking_id', $search );
				} elseif ( get_post_type( $search ) === MPHB()->postTypes()->payment()->getPostType() ) {
					$customWhere = $wpdb->prepare( "($wpdb->posts.ID = %d)", (int) $search );
				}
			}

			if ( ! empty( $customWhere ) ) {
				$where = " AND ({$customWhere}) ";
			}
		}

		return $where;
	}

	/**
	 *
	 * @global \WPDB $wpdb
	 * @param string    $join
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function extendSearchPostsJoin( $join, $wp_query ) {
		global $wpdb;
		if ( $this->isCurrentPage() && ! empty( $wp_query->query['s'] ) ) {
			$joinCount = (int) $wp_query->get( '_mphb_join_meta', 0 );
			for ( $i = 1; $i <= $joinCount; $i++ ) {
				$join .= " LEFT JOIN $wpdb->postmeta AS mphb_postmeta_{$i} ON $wpdb->posts.ID = mphb_postmeta_{$i}.post_id ";
			}
		}
		return $join;
	}

	/**
	 *
	 * @param string    $orderBy
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function extendPostsSearchOrderBy( $orderBy, $wp_query ) {
		// Prevent OrderBy Search terms
		return '';
	}

	/**
	 *
	 * @param array $views
	 */
	public function filterViews( $views ) {

		if ( isset( $views['mine'] ) ) {
			unset( $views['mine'] );
		}

		$viewsOrder = array(
			'all'                                 => 0,
			PaymentCPT\Statuses::STATUS_COMPLETED => 10,
			PaymentCPT\Statuses::STATUS_PENDING   => 20,
			PaymentCPT\Statuses::STATUS_FAILED    => 30,
			PaymentCPT\Statuses::STATUS_ABANDONED => 40,
			PaymentCPT\Statuses::STATUS_ON_HOLD   => 50,
			PaymentCPT\Statuses::STATUS_REFUNDED  => 60,
			PaymentCPT\Statuses::STATUS_CANCELLED => 70,
			'trash'                               => 500,
		);

		uksort(
			$views,
			function( $view1, $view2 ) use ( $viewsOrder ) {
				$view1Order = array_key_exists( $view1, $viewsOrder ) ? $viewsOrder[ $view1 ] : 999;
				$view2Order = array_key_exists( $view2, $viewsOrder ) ? $viewsOrder[ $view2 ] : 999;
				return $view1Order > $view2Order ? 1 : ( $view1Order == $view2Order ? 0 : -1 );
			}
		);

		return $views;
	}

	/**
	 * Prevent duplicates
	 *
	 * @global \WPDB $wpdb
	 * @param string $where
	 * @return string
	 */
	function searchDistinct( $where ) {

		if ( is_search() ) {
			return 'DISTINCT';
		}

		return $where;
	}

}
