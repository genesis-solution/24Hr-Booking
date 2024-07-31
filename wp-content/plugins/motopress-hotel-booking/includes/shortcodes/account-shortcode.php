<?php

namespace MPHB\Shortcodes;

use \MPHB\UsersAndRoles\Customers;

class AccountShortcode extends AbstractShortcode {

	protected $name = 'mphb_account';

	public $tab;

	private $errors;

	public function __construct() {

		parent::__construct();

		$this->errors = array();
	}

	public function addActions() {
		parent::addActions();
		add_action( 'init', array( $this, 'onInit' ) );
		add_action( 'wp_login_failed', array( $this, 'redirectOnFailedLogin' ) );
		add_action( 'wp_logout', array( $this, 'redirectAfterLogout' ) );
	}

	public function onInit() {
		$post = ! empty( $_POST ) ? wp_unslash( $_POST ) : array();

		if ( isset( $post['mphb_action'] ) && $post['mphb_action'] == 'update_customer' ) {

			if ( wp_verify_nonce( $post['_wpnonce'] ) ) {
				$customerId = isset( $post['customer_id'] ) ? (int) $post['customer_id'] : null;
				$redirectTo = isset( $post['redirect_to'] ) ? esc_url( $post['redirect_to'] ) : '';

				if ( ! $customerId ) {
					wp_redirect( $redirectTo );
					exit;
				}

				$userId = get_current_user_id();

				if ( ! $userId ) { // User logged out
					wp_redirect( $redirectTo );
					exit;
				}

				$customer = MPHB()->customers()->customer( $post );

				$updatedUser = MPHB()->customers()->updateLinkedUser( $userId, $customer );

				if ( is_wp_error( $updatedUser ) ) {
					$this->errors[] = $updatedUser;
					return;
				}

				$customer->setUserId( $userId );

				$updatedCustomer = MPHB()->customers()->updateData( $customer );

				if ( is_wp_error( $updatedCustomer ) ) {
					$this->errors[] = $updatedCustomer;
					return;
				}

				if ( $updatedCustomer ) {
					$redirectTo = add_query_arg( 'customer_updated', 'success', $redirectTo );
				}

				$changedPassword = $this->updatePassword();

				if ( $changedPassword ) {
					$redirectTo = add_query_arg( 'password_changed', 'success', $redirectTo );
				}

				if ( empty( $this->errors ) ) {
					wp_redirect( $redirectTo );
					exit;
				}
			}
		}
	}

	public function render( $atts, $content, $shortcodeName ) {
		$defaultAtts = array(
			'class' => '',
		);

		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		$wrapperClass = apply_filters( 'mphb_sc_account_wrapper_class', 'mphb_sc_account' );
		$wrapperClass = trim( $wrapperClass . ' ' . $atts['class'] );

		$customer = null;
		$user     = wp_get_current_user();
		$userId   = $user->ID;

		if ( $userId ) {
			$customer = MPHB()->customers()->findBy( 'user_id', $userId );
		}

		$this->tab = null != get_query_var( 'tab' ) ? get_query_var( 'tab' ) : 'dashboard';

		$output = '';

		ob_start();

		?>
		<div class="<?php echo esc_attr( $wrapperClass ); ?>">
			<?php $this->renderMenu( $userId ); ?>
			<div class="mphb-account-content">
				<?php $this->renderErrors(); ?>
				<?php $this->successMessage(); ?>
				<?php $this->renderLogin( $userId ); ?>
				<?php
				switch ( $this->tab ) {
					case 'dashboard':
						$this->renderDashboard( $user );
						break;
					case 'bookings':
						$this->renderBookings( $customer );
						break;
					case 'account-details':
						$this->renderDetails( $customer );
						break;
					default:
						$this->renderDashboard( $user );
						break;
				}
				?>
			</div>
		</div>
		<?php
		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}

	protected function renderDashboard( $user ) {
		mphb_get_template_part(
			'account/dashboard',
			array(
				'user'      => $user,
				'permalink' => get_permalink(),
			)
		);
	}

	protected function renderBookings( $customer ) {
		$bookings   = array();
		$baseLink   = mphb_create_url( 'bookings' );
		$totalPages = 1;
		$cur        = 1;

		if ( $customer ) {
			$postsPerPage = get_option( 'posts_per_page' );
			$postsPerPage = (int) apply_filters( 'mphb_booking_history_shortcode_posts_per_page', $postsPerPage );
			$curPage      = isset( $_GET['_page'] ) ? (int) $_GET['_page'] : 1;

			$offset = ( $curPage - 1 ) * $postsPerPage;

			$atts = array(
				'posts_per_page' => $postsPerPage,
				'offset'         => $offset,
			);

			$bookings = \MPHB\UsersAndRoles\Customers::findBookings( $customer->getId(), $atts );

			$totalPages = ceil( count( \MPHB\UsersAndRoles\Customers::findBookings( $customer->getId() ) ) / $postsPerPage );

			$cur = isset( $_GET['_page'] ) ? (int) $_GET['_page'] : 1;
		}

		mphb_get_template_part(
			'account/bookings',
			array(
				'customer'   => $customer,
				'bookings'   => $bookings,
				'baseLink'   => $baseLink,
				'totalPages' => $totalPages,
				'cur'        => $cur,
			)
		);
	}

	protected function renderDetails( $customer ) {
		mphb_get_template_part(
			'account/account-details',
			array(
				'customer' => $customer,
				'redirect' => mphb_create_url( 'account-details' ),
			)
		);
	}

	protected function renderErrors() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				?>
				<p class="mphb-data-incorrect"><?php echo esc_html( $error->get_error_message() ); ?></p>
				<?php
			}
		}

		if ( isset( $_GET['login_failed'] ) && $_GET['login_failed'] == 'error' ) {
			?>
			<p class="mphb-data-incorrect"><?php echo esc_html__( 'Invalid login or password.', 'motopress-hotel-booking' ); ?></p>
			<?php
		}
	}

	protected function successMessage() {
		if ( empty( $this->errors ) ) {
			if ( isset( $_GET['customer_updated'] ) && $_GET['customer_updated'] == 'success' ) {
				?>
				<p class="mphb-data-success"><?php echo esc_html__( 'Account data updated.', 'motopress-hotel-booking' ); ?></p>
				<?php
			}

			if ( isset( $_GET['password_changed'] ) && $_GET['password_changed'] == 'success' ) {
				?>
				<p class="mphb-data-success"><?php echo esc_html__( 'Password changed.', 'motopress-hotel-booking' ); ?></p>
				<?php
			}
		}
	}

	protected function renderMenu( $userId ) {
		if ( $userId ) {
			?>
			<nav class="mphb-account-menu">
				<ul>
					<li><a href="<?php echo esc_url( mphb_create_url( 'dashboard' ) ); ?>"><?php echo esc_html__( 'Dashboard', 'motopress-hotel-booking' ); ?></a></li>
					<li><a href="<?php echo esc_url( mphb_create_url( 'bookings' ) ); ?>"><?php echo esc_html__( 'Bookings', 'motopress-hotel-booking' ); ?></a></li>
					<li><a href="<?php echo esc_url( mphb_create_url( 'account-details' ) ); ?>"><?php echo esc_html__( 'Account', 'motopress-hotel-booking' ); ?></a></li>
					<li><a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php echo esc_html__( 'Logout', 'motopress-hotel-booking' ); ?></a></li>
				</ul>
			</nav>
			<?php
		}
	}

	protected function updatePassword() {
		$post = ! empty( $_POST ) ? wp_unslash( $_POST ) : array();

		if ( ! empty( $post['new_password'] ) && ! empty( $post['confirm_new_password'] ) && ! empty( $post['old_password'] ) ) {
			$user    = wp_get_current_user();
			$oldPass = $this->checkPassword( $post['old_password'], $user->ID );

			if ( $oldPass ) { // current password is correct
				if ( $post['new_password'] == $post['confirm_new_password'] ) { // Passwords are the same

					wp_set_password( $post['new_password'], $user->ID );

					wp_cache_delete( $user->ID, 'users' );
					wp_cache_delete( $user->user_login, 'userlogins' );
					wp_logout();

					$signon = wp_signon(
						array(
							'user_login'    => $user->user_login,
							'user_password' => $post['new_password'],
						),
						false
					);

					if ( is_wp_error( $signon ) ) {
						$this->errors[] = $signon;
						return false;
					}

					return true;
				} else {
					$this->errors[] = new \WP_Error( 'passwords_not_the_same', __( 'Passwords do not match.', 'motopress-hotel-booking' ) );
				}
			} else {
				$this->errors[] = new \WP_Error( 'not_authentificated', __( 'Please, provide a valid current password.', 'motopress-hotel-booking' ) );
			}
		}

		return false;
	}

	private function checkPassword( $plainPassword, $userId ) {
		$userData = get_user_by( 'id', $userId );
		$hash     = $userData->user_pass;

		return wp_check_password( $plainPassword, $hash, $userId );
	}

	protected function renderLogin( $userId ) {
		if ( ! $userId ) {
			?>
			<div class="mphb-login-form">
				<?php wp_login_form(); ?>
				<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Lost your password?', 'motopress-hotel-booking' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	 *
	 * @since 4.2.1
	 */
	public function redirectOnFailedLogin() {

		$referrer = wp_get_referer();

		if ( false === $referrer ) {
			return;
		}

		$accountPageId = MPHB()->settings()->pages()->getMyAccountPageId();
		$page          = get_post( $accountPageId );
		$slug          = $page->post_name;

		if ( strstr( $referrer, $slug ) ) {

			$redirectTo = add_query_arg( 'login_failed', 'error', $referrer );
			wp_safe_redirect( $redirectTo );
			exit;
		}
	}

	/**
	 *
	 * @since 4.2.1
	 */
	public function redirectAfterLogout() {

		$referrer = wp_get_referer();

		if ( false === $referrer ) {
			return;
		}

		$accountPageId = MPHB()->settings()->pages()->getMyAccountPageId();
		$page          = get_post( $accountPageId );
		$slug          = $page->post_name;

		if ( strstr( $referrer, $slug ) ) {

			wp_safe_redirect( $referrer );
			exit;
		}
	}
}
