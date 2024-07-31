<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\PostTypes\BookingCPT;
use \MPHB\Utils\BookingUtils;
use \MPHB\Utils\DateUtils;
use \MPHB\Views;
use \MPHB\Entities;

class BookingManageCPTPage extends ManageCPTPage {

	protected function addActionsAndFilters() {
		parent::addActionsAndFilters();

		$this->addTitleAction( __( 'New Booking', 'motopress-hotel-booking' ), add_query_arg( 'page', 'mphb_add_new_booking', admin_url( 'admin.php' ) ) );

		add_filter( 'request', array( $this, 'filterCustomOrderBy' ) );

		add_filter( 'views_edit-mphb_booking', array( $this, 'addImportedView' ) );

		add_filter( 'post_row_actions', array( $this, 'filterRowActions' ) );
		add_action( 'restrict_manage_posts', array( $this, 'addAccommodationsFilter' ) );
		add_action( 'restrict_manage_posts', array( $this, 'fillDependencedData' ) );

		if ( is_admin() ) {
			add_filter( 'posts_join', array( $this, 'extendSearchPostsJoin' ), 10, 2 );
			add_filter( 'posts_search', array( $this, 'extendPostsSearch' ), 10, 2 );
			add_filter( 'posts_search_orderby', array( $this, 'extendPostsSearchOrderBy' ), 10, 2 );
			add_filter( 'posts_distinct', array( $this, 'searchDistinct' ) );
		}

		// Bulk actions
		add_filter( 'bulk_actions-edit-' . $this->postType, array( $this, 'filterBulkActions' ) );
		add_action( 'admin_notices', array( $this, 'bulkAdminNotices' ) );
		add_action( 'admin_footer', array( $this, 'bulkAdminScript' ) );
		add_action( 'load-edit.php', array( $this, 'bulkAction' ) );
	}

	/**
	 * @since 3.7.3 (replaced the method enqueueAdminScripts())
	 */
	public function enqueueScripts() {
		parent::enqueueScripts();

		if ( $this->isCurrentPage() ) {
			MPHB()->getAdminScriptManager()->enqueue();
		}
	}

	public function filterColumns( $columns ) {

		if ( isset( $columns['title'] ) ) {
			unset( $columns['title'] );
		}

		$customColumns = array(
			'id'                => __( 'ID', 'motopress-hotel-booking' ),
			'status'            => __( 'Status', 'motopress-hotel-booking' ),
			'check_in_out_date' => __( 'Check-in / Check-out', 'motopress-hotel-booking' ),
			'guests'            => __( 'Guests', 'motopress-hotel-booking' ),
			'customer_info'     => __( 'Customer Info', 'motopress-hotel-booking' ),
			'price'             => __( 'Price', 'motopress-hotel-booking' ),
			'room_type'         => __( 'Accommodation', 'motopress-hotel-booking' ),
			'mphb_date'         => __( 'Date', 'motopress-hotel-booking' ),
		);

		$offset  = array_search( 'date', array_keys( $columns ) ); // Set custom columns position before "DATE" column
		$columns = array_slice( $columns, 0, $offset, true ) + $customColumns + array_slice( $columns, $offset, count( $columns ) - 1, true );

		unset( $columns['date'] );
		return $columns;
	}

	public function filterSortableColumns( $columns ) {

		$columns['id']                = 'ID';
		$columns['check_in_out_date'] = 'mphb_check_in_out_date';

		return $columns;
	}

	public function renderColumns( $column, $postId ) {
		$booking = MPHB()->getBookingRepository()->findById( $postId );

		if ( is_null( $booking ) ) {
			return;
		}

		switch ( $column ) {
			case 'id':
				printf( '<a href="%s"><strong>' . esc_html( '#%s' ) . '</strong></a>', esc_url( get_edit_post_link( $postId ) ), esc_html( $postId ) );
				break;
			case 'status':
				$status = $booking->getStatus();
				?>
				<span class="column-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( mphb_get_status_label( $status ) ); ?></span>
				<?php
				if ( $status === BookingCPT\Statuses::STATUS_PENDING_PAYMENT ) {
					$payments = MPHB()->getPaymentRepository()->findAll(
						array(
							'booking_id'  => $booking->getId(),
							'post_status' => \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING,
						)
					);
					foreach ( $payments as $payment ) {
						echo sprintf( '(<small><a href="%s">#%s</a></small>)', esc_url( get_edit_post_link( $payment->getId() ) ), esc_html( $payment->getId() ) );
					}
				}
				if ( in_array( $status, array( BookingCPT\Statuses::STATUS_PENDING_USER, BookingCPT\Statuses::STATUS_PENDING_PAYMENT ) ) ) {
					$expireTime = $booking->retrieveExpiration( $status === BookingCPT\Statuses::STATUS_PENDING_USER ? 'user' : 'payment' );
					if ( $expireTime ) {
						$currentTime = time();
						echo '<br/>';
						echo '<small>';
						if ( $expireTime > $currentTime ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							printf( esc_html__( 'Expire %s', 'motopress-hotel-booking' ), human_time_diff( $currentTime, $expireTime ) );
						} else {
							esc_html_e( 'Expired', 'motopress-hotel-booking' );
						}
						echo '</small>';
					}
				}
				break;
			case 'guests':
				$reservedRooms = $booking->getReservedRooms();
				if ( ! empty( $reservedRooms ) && ! $booking->isImported() ) {
					$adultsTotal   = 0;
					$childrenTotal = 0;
					foreach ( $reservedRooms as $reservedRoom ) {
						$adultsTotal   += $reservedRoom->getAdults();
						$childrenTotal += $reservedRoom->getChildren();
					}
					esc_html_e( 'Adults: ', 'motopress-hotel-booking' );
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $adultsTotal . '<br/>';
					if ( $childrenTotal > 0 ) {
						esc_html_e( 'Children: ', 'motopress-hotel-booking' );
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $childrenTotal . '<br/>';
					}
					echo '<br/>';
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo static::EMPTY_VALUE_PLACEHOLDER;
				}
				break;
			case 'check_in_out_date':
				$checkInDate  = $booking->getCheckInDate();
				$checkOutDate = $booking->getCheckOutDate();

				echo '<div class="check_in_out_date-wrapper">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( ! is_null( $checkInDate ) ) {
					echo '<time title="' . esc_attr( \MPHB\Utils\DateUtils::formatDateWPFront( $checkInDate ) ) . '">' .
						date_i18n( 'M j', $checkInDate->format( 'U' ) ) .
						'<span class="mphb-booking-year">' . date_i18n( 'Y', $checkInDate->format( 'U' ) ) .
					'</span></time>';
				} else {
					'<span aria-hidden="true">' . static::EMPTY_VALUE_PLACEHOLDER . '</span>';
				}
				echo ' - ';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( ! is_null( $checkOutDate ) ) {
					echo '<time title="' . esc_attr( \MPHB\Utils\DateUtils::formatDateWPFront( $checkOutDate ) ) . '">' .
						date_i18n( 'M j', $checkOutDate->format( 'U' ) ) .
						'<span class="mphb-booking-year">' . date_i18n( 'Y', $checkOutDate->format( 'U' ) ) .
					'</span></time>';
				} else {
					echo '<span aria-hidden="true">' . static::EMPTY_VALUE_PLACEHOLDER . '</span>';
				}
				echo '</div>';

				if ( ! is_null( $checkInDate ) && ! is_null( $checkOutDate ) ) {
					// There is a bug on Windows platforms: the result is always 6015 days.
					// See http://php.net/manual/ru/datetime.diff.php#102507
					// (Found on Windows 7, PHP 5.3.5)
					$nights = \MPHB\Utils\DateUtils::calcNights( $checkInDate, $checkOutDate );
					?>
					<span class="mphb-booking-nights"><?php echo esc_html( sprintf( _n( '%s night', '%s nights', $nights, 'motopress-hotel-booking' ), $nights ) ); ?></span>
					<?php
				}

				break;
			case 'customer_info':
				if ( $booking->isImported() ) {
					$summary     = $booking->getICalSummary();
					$description = $booking->getICalDescription();

					$info = '';

					if ( ! empty( $summary ) ) {
						$info = sprintf( __( 'Summary: %s.', 'motopress-hotel-booking' ), $summary );
					}

					if ( ! empty( $description ) ) {
						if ( ! empty( $info ) ) {
							$info .= '<br />';
						}
						$info .= str_replace( "\n", '<br />', trim( $description, '"' ) );
					}

					if ( ! empty( $info ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $info;
					} else {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo static::EMPTY_VALUE_PLACEHOLDER;
					}
				} else {
					$customer = $booking->getCustomer();

					if ( $customer ) :
						?>
						<?php
							$customerInfo = esc_html( $customer->getName() );

						if ( ! empty( $customer->getEmail() ) ) {

							$customer_email = sanitize_email( $customer->getEmail() );

							$customerInfo .= ! empty( $customerInfo ) ? '<br />' : '';
							$customerInfo .= '<a href="mailto:' . esc_attr( $customer_email ) . '">'
									. esc_html( $customer_email )
								. '</a>';
						}

						if ( ! empty( $customer->getPhone() ) ) {
							$customerInfo .= ! empty( $customerInfo ) ? '<br />' : '';
							$customerInfo .= '<a href="tel:' . esc_attr( $customer->getPhone() ) . '">'
									. esc_html( $customer->getPhone() )
								. '</a>';
						}

							$customerInfo = trim( $customerInfo );

						if ( ! empty( $customerInfo ) ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $customerInfo;
						} else {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo static::EMPTY_VALUE_PLACEHOLDER;
						}
						?>
					<?php else : ?>
						<span aria-hidden="true">
						<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo static::EMPTY_VALUE_PLACEHOLDER;
						?>
							</span>
						<?php
					endif;
				}
				break;
			case 'price':
				// Don't show the price for imported bookings
				if ( $booking->isImported() ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo static::EMPTY_VALUE_PLACEHOLDER;
					break;
				}

				Views\BookingView::renderTotalPriceHTML( $booking );
				echo '<br/>';
				$payments  = MPHB()->getPaymentRepository()->findAll(
					array(
						'booking_id'  => $booking->getId(),
						'post_status' => \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED,
					)
				);
				$totalPaid = 0.0;
				foreach ( $payments as $payment ) {
					$totalPaid += $payment->getAmount();
				}
				$paidLabel = sprintf( __( 'Paid: %s', 'motopress-hotel-booking' ), mphb_format_price( $totalPaid ) );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				printf( '<a href="%1$s">%2$s</a>', esc_url( MPHB()->postTypes()->payment()->getManagePage()->getUrl( array( 's' => $booking->getId() ) ) ), $paidLabel );
				break;
			case 'room_type':
				$reservedTypes = BookingUtils::getReservedRoomTypesList( $booking, null, false );
				$links         = array_map(
					function ( $roomTypeId, $title ) {
						return '<a href="' . esc_url( get_edit_post_link( $roomTypeId ) ) . '">' . esc_html( $title ) . '</a>';
					},
					array_keys( $reservedTypes ),
					$reservedTypes
				);
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo ! empty( $links ) ? implode( ', ', $links ) : self::EMPTY_VALUE_PLACEHOLDER;
				break;
			case 'mphb_date':
				?>
				<abbr title="<?php echo esc_attr( get_the_date( MPHB()->settings()->dateTime()->getDateTimeFormatWP(), $postId ) ); ?>">
					<?php echo get_the_date( 'Y/m/d', $postId ); ?>
				</abbr>
				<?php
				break;
		}
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

	/**
	 *
	 * @global \WP_Query $wp_query
	 */
	public function fillDependencedData() {
		global $wp_query;

		if ( ! $this->isCurrentPage() ) {
			return;
		}

		MPHB()->getReservedRoomRepository()->fillBookingReservedRooms( $wp_query->posts );
	}

	public function filterBulkActions( $bulkActions ) {
		if ( isset( $bulkActions['edit'] ) ) {
			unset( $bulkActions['edit'] );
		}
		return $bulkActions;
	}

	/**
	 * Add extra bulk action options to change booking status.
	 *
	 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031.
	 */
	public function bulkAdminScript() {
		if ( $this->isCurrentPage() ) {
			$optionText = __( 'Set to %s', 'motopress-hotel-booking' );
			?>
			<script type="text/javascript">
				(function( $ ) {
					$( function() {
						var options = [
							$( '<option />', {
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								value: 'set-status-<?php echo BookingCPT\Statuses::STATUS_PENDING; ?>',
								text: '<?php echo esc_html( sprintf( $optionText, mphb_get_status_label( BookingCPT\Statuses::STATUS_PENDING ) ) ); ?>'
							} ),
							$( '<option />', {
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								value: 'set-status-<?php echo BookingCPT\Statuses::STATUS_PENDING_USER; ?>',
								text: '<?php echo esc_html( sprintf( $optionText, mphb_get_status_label( BookingCPT\Statuses::STATUS_PENDING_USER ) ) ); ?>'
							} ),
							$( '<option />', {
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								value: 'set-status-<?php echo BookingCPT\Statuses::STATUS_ABANDONED; ?>',
								text: '<?php echo esc_html( sprintf( $optionText, mphb_get_status_label( BookingCPT\Statuses::STATUS_ABANDONED ) ) ); ?>'
							} ),
							$( '<option />', {
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								value: 'set-status-<?php echo BookingCPT\Statuses::STATUS_CONFIRMED; ?>',
								text: '<?php echo esc_html( sprintf( $optionText, mphb_get_status_label( BookingCPT\Statuses::STATUS_CONFIRMED ) ) ); ?>'
							} ),
							$( '<option />', {
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								value: 'set-status-<?php echo BookingCPT\Statuses::STATUS_CANCELLED; ?>',
								text: '<?php echo esc_html( sprintf( $optionText, mphb_get_status_label( BookingCPT\Statuses::STATUS_CANCELLED ) ) ); ?>'
							} )
						];

						var topBulkSelect = $( 'select[name="action"]' );
						var bottomBulkSelect = $( 'select[name="action2"]' );

						$.each( options, function( index, option ) {
							topBulkSelect.append( option.clone() );
							bottomBulkSelect.append( option.clone() );
						} );

					} );
				})( jQuery )
			</script>
			<?php
		}
	}

	/**
	 * Process the new bulk actions for changing booking status.
	 */
	public function bulkAction() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		if ( strpos( $action, 'set-status-' ) === false ) {
			return;
		}

		$allowedStatuses = MPHB()->postTypes()->booking()->statuses()->getStatuses();

		$newStatus    = substr( $action, 11 );
		$reportAction = 'setted-status-' . $newStatus;

		if ( ! isset( $allowedStatuses[ $newStatus ] ) ) {
			return;
		}

		check_admin_referer( 'bulk-posts' );

		$postIds = isset( $_REQUEST['post'] ) ? array_map( 'absint', (array) $_REQUEST['post'] ) : array();

		if ( empty( $postIds ) ) {
			return;
		}

		$changed = 0;
		foreach ( $postIds as $postId ) {

			$booking = MPHB()->getBookingRepository()->findById( $postId, true );
			$booking->setStatus( $newStatus );

			if ( MPHB()->getBookingRepository()->save( $booking ) ) {
				$changed++;
			}
		}

		$queryArgs = array(
			$reportAction => true,
			'changed'     => $changed,
			'ids'         => join( ',', $postIds ),
			'paged'       => $wp_list_table->get_pagenum(),
		);

		if ( isset( $_GET['post_status'] ) ) {
			$queryArgs['post_status'] = sanitize_text_field( wp_unslash( $_GET['post_status'] ) );
		}

		$sendback = $this->getUrl( $queryArgs );

		wp_redirect( esc_url_raw( $sendback ) );
		exit;
	}

	/**
	 * Show message that booking status changed for number of bookings.
	 */
	public function bulkAdminNotices() {
		if ( $this->isCurrentPage() ) {
			// Check if any status changes happened
			foreach ( MPHB()->postTypes()->booking()->statuses()->getStatuses() as $slug => $details ) {

				if ( isset( $_REQUEST[ 'setted-status-' . $slug ] ) ) {

					$number  = isset( $_REQUEST['changed'] ) ? absint( $_REQUEST['changed'] ) : 0;
					$message = sprintf( _n( 'Booking status changed.', '%s booking statuses changed.', $number, 'motopress-hotel-booking' ), number_format_i18n( $number ) );
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<div class="updated"><p>' . $message . '</p></div>';

					break;
				}
			}
		}
	}

	public function addAccommodationsFilter() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		// WPML note: with "All languages" selected the method will return both
		// original and translated room types
		$roomTypes = MPHB()->getRoomTypeRepository()->getIdTitleList();

		$filterRoomTypes = array();

		foreach ( $roomTypes as $roomTypeId => $roomTitle ) {
			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

			if ( is_null( $roomType ) || array_key_exists( $roomType->getOriginalId(), $filterRoomTypes ) ) {
				continue;
			}

			$originalId = $roomType->getOriginalId();

			// Force all room types to show on main language
			if ( $originalId != $roomTypeId ) {
				$roomType  = MPHB()->translation()->translateRoomType( $roomType, MPHB()->translation()->getDefaultLanguage() );
				$roomTitle = $roomType->getTitle();
			}

			$filterRoomTypes[ $originalId ] = $roomTitle;
		}

		$selectedId = isset( $_GET['mphb_room_type_id'] ) ? absint( $_GET['mphb_room_type_id'] ) : 0;

		if ( $selectedId != 0 && ! array_key_exists( $selectedId, $filterRoomTypes ) ) {
			$selectedId = 0;
		}

		echo '<select name="mphb_room_type_id">';
		echo '<option value="0" ' . selected( $selectedId, 0, false ) . '>', esc_html__( 'All accommodation types', 'motopress-hotel-booking' ), '</option>';

		foreach ( $filterRoomTypes as $roomTypeId => $roomTitle ) {
			echo '<option value="' . esc_attr( $roomTypeId ) . '" ' . selected( $selectedId, $roomTypeId, false ) . '>', esc_html( $roomTitle ), '</option>';
		}

		echo '</select>';
	}

	public function addImportedView( $views ) {
		$importedCount = MPHB()->getBookingRepository()->getImportedCount();

		if ( $importedCount == 0 ) {
			return $views;
		}

		$viewUrl  = $this->getUrl( array( 'post_status' => 'imported' ) );
		$linkAtts = $this->isImportedView() ? ' class="current" aria-current="page"' : '';

		// translators: The number of imported bookings: "Imported <span>(11)</span>"
		$label = sprintf( __( 'Imported %s', 'motopress-hotel-booking' ), '<span class="count">(%s)</span>' );
		$label = sprintf( $label, number_format_i18n( $importedCount ) );

		// Build link like in \WP_Plugins_List_Table::get_views() in
		// wp-admin/includes/class-wp-plugins-list-table.php
		$view = sprintf( '<a href="%s"%s>%s</a>', esc_url( $viewUrl ), $linkAtts, $label );

		$views['mphb_imported'] = $view;

		return $views;
	}

	public function isImportedView() {
		return isset( $_GET['post_status'] ) && $_GET['post_status'] === 'imported';
	}

	protected function isBookingsQuery( $query ) {
		return $this->isCurrentPage()
			// Don't break reserved rooms/room types queries on current page
			&& isset( $query->query['post_type'] )
			&& $query->query['post_type'] === MPHB()->postTypes()->booking()->getPostType();
	}

	/**
	 * Replace the search in post_title, post_excerpt and post_content.
	 *
	 * @param string    $where
	 * @param \WP_Query $query
	 * @return string
	 *
	 * @global \WPDB $wpdb
	 */
	public function extendPostsSearch( $where, $query ) {
		global $wpdb;

		if ( ! $this->isBookingsQuery( $query ) ) {
			return $where;
		}

		$search = isset( $query->query['s'] ) ? trim( $query->query['s'] ) : '';

		// Apply search filter
		if ( $search !== '' ) {
			$query->set( 'mphb_join_booking_meta', true );

			$alternatives = array();

			// Search by ID and price
			if ( is_numeric( $search ) ) {
				$id = intval( $search );

				$price = mphb_format_price(
					floatval( $search ),
					array(
						'as_html'         => false,
						'currency_symbol' => '',
					)
				);
				$price = mphb_trim_decimal_zeros( $price );

				$alternatives[] = $wpdb->prepare( "{$wpdb->posts}.ID = %d", $id );
				$alternatives[] = $wpdb->prepare( "(mphb_bookmeta.meta_key = 'mphb_total_price' AND mphb_bookmeta.meta_value = %s)", $price );
			}

			// Search any other match
			$searchVariants = array( $search );

			if ( DateUtils::isDate( $search ) ) {
				$searchVariants[] = DateUtils::convertDateFormat( $search, MPHB()->settings()->dateTime()->getDateFormat(), MPHB()->settings()->dateTime()->getDateTransferFormat() );
			}

			$countryCode = MPHB()->settings()->main()->getCountriesBundle()->getCountryCode( $search );

			if ( $countryCode !== false ) {
				$searchVariants[] = $countryCode;
			}

			if ( count( $searchVariants ) == 1 ) {
				// The $search is neither date, nor country code
				$alternatives[] = $wpdb->prepare( "(mphb_bookmeta.meta_key LIKE 'mphb_%' AND mphb_bookmeta.meta_value = %s)", $search );
			} else {
				// The $search may be date, country code or both
				$searchVariants = esc_sql( $searchVariants );
				$searchValues   = "'" . implode( "', '", $searchVariants ) . "'";

				$alternatives[] = "(mphb_bookmeta.meta_key LIKE 'mphb_%' AND mphb_bookmeta.meta_value IN ({$searchValues}))";
			}

			// Add all alternatives to WHERE statement
			$where = ' AND (' . implode( ' OR ', $alternatives ) . ')';
		}

		// Apply accommodation filter
		if ( ! empty( $_GET['mphb_room_type_id'] ) ) {
			$query->set( 'mphb_join_reserved_rooms', true );

			$roomTypeId = absint( $_GET['mphb_room_type_id'] );
			$where     .= $wpdb->prepare( ' AND mphb_rooms_meta.meta_value = %s', $roomTypeId );
		}

		// Filter imported bookings
		if ( $this->isImportedView() ) {
			// Show only imported bookings
			$where .= " AND (mphb_sync_ids.meta_value IS NOT NULL AND mphb_sync_ids.meta_value != '')";
		} elseif ( ! MPHB()->settings()->main()->displayImportedBookings() ) {
			// Remove imported bookings from the booking list table
			$where .= " AND (mphb_sync_ids.meta_value IS NULL OR mphb_sync_ids.meta_value = '')";
		}

		return $where;
	}

	/**
	 * @param string    $join
	 * @param \WP_Query $query
	 * @return string
	 *
	 * @global \WPDB $wpdb
	 */
	public function extendSearchPostsJoin( $join, $query ) {
		global $wpdb;

		if ( ! $this->isBookingsQuery( $query ) ) {
			return $join;
		}

		$search = isset( $query->query['s'] ) ? trim( $query->query['s'] ) : '';

		$joinBookingMeta   = (bool) $query->get( 'mphb_join_booking_meta', false );
		$joinReservedRooms = (bool) $query->get( 'mphb_join_reserved_rooms', false );

		// Add join for search
		if ( $search !== '' && $joinBookingMeta ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} AS mphb_bookmeta ON {$wpdb->posts}.ID = mphb_bookmeta.post_id ";
		}

		// Add joins for accommodation filter
		if ( ! empty( $_GET['mphb_room_type_id'] ) && $joinReservedRooms ) {
			$join .= " INNER JOIN {$wpdb->posts} AS mphb_reserved_rooms ON {$wpdb->posts}.ID = mphb_reserved_rooms.post_parent"
				. " INNER JOIN {$wpdb->postmeta} AS mphb_reserved_rooms_meta ON mphb_reserved_rooms.ID = mphb_reserved_rooms_meta.post_id AND mphb_reserved_rooms_meta.meta_key = '_mphb_room_id'"
				. " INNER JOIN {$wpdb->posts} AS mphb_rooms ON mphb_reserved_rooms_meta.meta_value = mphb_rooms.ID"
				. " INNER JOIN {$wpdb->postmeta} AS mphb_rooms_meta ON mphb_rooms.ID = mphb_rooms_meta.post_id AND mphb_rooms_meta.meta_key = 'mphb_room_type_id'";
		}

		// Add joins to remove imported bookings from bookings list table or show only imported bookings
		if ( ! MPHB()->settings()->main()->displayImportedBookings() || $this->isImportedView() ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} AS mphb_sync_ids ON {$wpdb->posts}.ID = mphb_sync_ids.post_id AND mphb_sync_ids.meta_key = '_mphb_sync_id'";
		}

		return $join;
	}

	public function extendPostsSearchOrderBy( $orderBy, $wp_query ) {
		// Prevent OrderBy Search terms
		return '';
	}

	public function filterCustomOrderBy( $vars ) {
		if ( $this->isCurrentPage() && isset( $vars['orderby'] ) ) {
			switch ( $vars['orderby'] ) {
				case 'mphb_check_in_out_date':
					$vars = array_merge(
						$vars,
						array(
							'meta_key'  => 'mphb_check_in_date',
							'orderby'   => 'meta_value',
							'meta_type' => 'DATE',
						)
					);
					break;

				case 'mphb_room_id':
					$vars = array_merge(
						$vars,
						array(
							'meta_key' => '',
							'orderby'  => 'mphb_room_id',
						)
					);
					break;
			}
		}

		return $vars;
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
			'all'                                       => 0,
			BookingCPT\Statuses::STATUS_CONFIRMED       => 10,
			BookingCPT\Statuses::STATUS_ABANDONED       => 20,
			BookingCPT\Statuses::STATUS_CANCELLED       => 30,
			BookingCPT\Statuses::STATUS_PENDING_USER    => 40,
			BookingCPT\Statuses::STATUS_PENDING_PAYMENT => 50,
			BookingCPT\Statuses::STATUS_PENDING         => 60,
			'trash'                                     => 500,
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
