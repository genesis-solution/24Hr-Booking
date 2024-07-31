<?php

namespace MPHB\Admin\MenuPages;

use MPHB\UsersAndRoles;
use MPHB\Admin\CustomersListTable;
use MPHB\Admin\Fields\FieldFactory;

/**
 *
 * @since 4.2.0
 */
class CustomersMenuPage extends AbstractMenuPage {

	/**
	 * @var \MPHB\UsersAndRoles\Customers\CustomersListTable
	 */
	protected $table;

	/**
	 * @var bool
	 */
	private $isView;

	/**
	 * @var array
	 */
	public $errors;

	private $customer;

	private $fields = array();


	public function __construct( $name, $atts = array() ) {

		parent::__construct( $name, $atts );

		$this->isView = isset( $_GET['customer_id'] );
	}

	public function addActions() {
		parent::addActions();

		add_action( 'init', array( $this, 'save' ) );

		add_action( 'admin_menu', array( $this, 'controlAccess' ), 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
	}

	public function controlAccess() {
		if ( $this->isView && ! current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::EDIT_CUSTOMER ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'motopress-hotel-booking' ) );
		}
	}

	public function enqueueAdminScripts() {

		if ( ! $this->isCurrentPage() ) {
			return;
		}

		MPHB()->getAdminScriptManager()->enqueue();
	}

	public function save() {

		// Save customer
		if ( isset( $_POST['save'] ) ) {

			$customerId = isset( $_GET['customer_id'] ) ? (int) $_GET['customer_id'] : null;

			if ( ! $customerId ) {
				return;
			}

			if ( ! current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::EDIT_CUSTOMER ) ) {
				return;
			}

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
				return;
			}

			$post = isset( $_POST ) ? wp_unslash( $_POST ) : array();

			array_map(
				function( $item, $key ) use ( &$customerAtts ) {
					$customerAtts[ $key ] = $item;
				},
				$post,
				array_keys( $post )
			);

			$customerAtts['customer_id'] = $customerId;

			$customer = MPHB()->customers()->customer( $customerAtts );

			/*
			$userId = $customer->getUserId();

			if( $userId ) {
				$updatedUser = MPHB()->customers()->updateLinkedUser( $userId, $customer );

				if( is_wp_error( $updatedUser ) ) {
					$this->errors[] = $updatedUser;
					return;
				}
			}*/

			$updated = MPHB()->customers()->updateData( $customer );

			if ( is_wp_error( $updated ) ) {
				$this->errors[] = $updated;
				return;
			}

			if ( $updated ) {
				$url = add_query_arg(
					array(
						'page'             => 'mphb_customers',
						'customer_id'      => $customerId,
						'customer_updated' => 1,
					),
					admin_url( 'admin.php' )
				);
			} else {
				$url = add_query_arg(
					array(
						'page'        => 'mphb_customers',
						'customer_id' => $customerId,
					),
					admin_url( 'admin.php' )
				);
			}

			wp_redirect( $url );
			exit;
		}
	}

	public function onLoad() {

		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$this->table = new CustomersListTable();

		if ( $this->isView ) {

			$customerId = empty( $_GET['customer_id'] ) ? 0 : absint( $_GET['customer_id'] );

			$customer = UsersAndRoles\Customers::findById( $customerId );

			$this->customer = $customer;

			$this->fields[] = array(
				__( 'User ID', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'user_id',
					array(
						'type' => 'text',
					),
					$customer->getUserId()
				),
			);

			$this->fields[] = array(
				__( 'Username', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'username',
					array(
						'type'     => 'text',
						'readonly' => true,
					),
					$customer->getUserName()
				),
			);

			$this->fields[] = array(
				__( 'First Name', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'first_name',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getFirstName()
				),
			);

			$this->fields[] = array(
				__( 'Last Name', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'last_name',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getLastName()
				),
			);

			$this->fields[] = array(
				__( 'Email', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'email',
					array(
						'type'    => 'email',
						'default' => '',
					),
					$customer->getEmail()
				),
			);

			$this->fields[] = array(
				__( 'Phone', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'phone',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getPhone()
				),
			);

			$this->fields[] = array(
				__( 'Address', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'address1',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getAddress1()
				),
			);

			$this->fields[] = array(
				__( 'State / County', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'state',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getState()
				),
			);

			$this->fields[] = array(
				__( 'Country', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'country',
					array(
						'type'    => 'select',
						'list'    => array( '' => __( '— Select —', 'motopress-hotel-booking' ) ) + MPHB()->settings()->main()->getCountriesBundle()->getCountriesList(),
						'default' => '',
					),
					strtoupper( $customer->getCountry() )
				),
			);

			$this->fields[] = array(
				__( 'City', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'city',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getCity()
				),
			);

			$this->fields[] = array(
				__( 'Postcode', 'motopress-hotel-booking' ),
				FieldFactory::create(
					'zip',
					array(
						'type'    => 'text',
						'default' => '',
					),
					$customer->getZip()
				),
			);
		}
	}

	public function render() {
		?>
		<div class="wrap">
		<?php
		if ( $this->isView ) {
			?>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Customer', 'motopress-hotel-booking' ); ?></h1>

			<a class="page-title-action wp-exclude-emoji" href="<?php echo esc_url( $this->getUrl() ); ?>"><?php esc_html_e( 'Back', 'motopress-hotel-booking' ); ?> &#10548;&#xFE0E;</a>
			<?php
			if ( $this->customer->getUserId() ) {
				?>
				   <a class="page-title-action" href="<?php echo admin_url( sprintf( 'user-edit.php?user_id=%d', $this->customer->getUserId() ) ); ?>"><?php echo esc_html__( 'Edit User Profile', 'motopress-hotel-booking' ); ?></a>
				<?php
			}
			?>
			<hr class="wp-header-end" />
			
			<?php
			if ( ! empty( $this->errors ) ) {
				foreach ( $this->errors as $key => $error ) {
					?>
				   <div class="error notice notice-error is-dismissible"><p><?php echo esc_html( $error->get_error_message() ); ?></p></div>
					<?php
				}
			}

			if ( isset( $_GET['customer_updated'] ) && $_GET['customer_updated'] == 1 ) {
				?>
				<div class="updated notice notice-success is-dismissible"><p><?php echo esc_html__( 'Customer data updated.', 'motopress-hotel-booking' ); ?></p></div>
				<?php
			}

			if ( isset( $_GET['user_linked'] ) && $_GET['user_linked'] == 1 ) {
				?>
				<div class="updated notice notice-success is-dismissible"><p><?php echo esc_html__( 'User account updated.', 'motopress-hotel-booking' ); ?></p></div>
				<?php
			}
			?>
			<form method="POST" action="">
				
				<?php
				wp_nonce_field();

				if ( ! empty( $this->fields ) ) {
					?>
					<table class="form-table">
						<tbody>
						<?php
						foreach ( $this->fields as $field ) {

							list( $fLabel, $fHtml ) = $field;
							?>
							<tr class="mphb-customer-field-wrap">
								<th>
								<?php printf( '%s<br />', esc_html( $fLabel ) ); ?>
								</th>
								<td>
								<?php echo $fHtml->render(); ?>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
					<?php
				}
				?>
				<p>
					<input type="submit" class="button button-primary" name="save" value="<?php echo esc_html__( 'Update', 'motopress-hotel-booking' ); ?>" />
				</p>
			</form>
			<?php
		} else {
			?>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'motopress-hotel-booking' ); ?></h1>

			<hr class="wp-header-end" />
			<?php
			$this->table->prepare_items();
			$this->table->display();
		}
		?>
		</div>
		<?php
	}

	protected function getMenuTitle() {
		return __( 'Customers', 'motopress-hotel-booking' );
	}

	protected function getPageTitle() {
		return __( 'Customers', 'motopress-hotel-booking' );
	}
}
