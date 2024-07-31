<?php

/**
 *
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $customer ) {
	?>
	<form method="post" class="mphb-account-details-form">
		<input type="hidden" name="mphb_action" value="update_customer" />
		<input type="hidden" name="customer_id" value="<?php echo (int) $customer->getId(); ?>" />
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>" />
		<?php wp_nonce_field(); ?>

		<div class="mphb-account-details">
			<p class="mphb-customer-first-name">
				<label for="mphb-first-name"><?php echo esc_html__( 'First Name', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-first-name" name="first_name" type="text" value="<?php echo esc_attr( $customer->getFirstName() ); ?>" />
			</p>
			<p class="mphb-customer-last-name">
				<label for="mphb-last-name"><?php echo esc_html__( 'Last Name', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-last-name" name="last_name" type="text" value="<?php echo esc_attr( $customer->getLastName() ); ?>" />
			</p>
			<p class="mphb-customer-username">
				<label for="mphb-username"><?php echo esc_html__( 'Username', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-username" name="username" type="text" value="<?php echo esc_attr( $customer->getUserName() ); ?>" readonly="true" />
			</p>
			<p class="mphb-customer-email">
				<label for="mphb-email"><?php echo esc_html__( 'Email', 'motopress-hotel-booking' ); ?></label> <abbr title="<?php esc_html_e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
				<input id="mphb-email" name="email" type="email" required value="<?php echo esc_attr( $customer->getEmail() ); ?>" />
			</p>
			<p class="mphb-customer-phone">
				<label for="mphb-phone"><?php echo esc_html__( 'Phone', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-phone" name="phone" type="text" value="<?php echo esc_attr( $customer->getPhone() ); ?>" />
			</p>
			<p class="mphb-customer-address1">
				<label for="mphb-address1"><?php echo esc_html__( 'Address', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-address1" name="address1" type="text" value="<?php echo esc_attr( $customer->getAddress1() ); ?>" />
			</p>
			<p  class="mphb-customer-country">
				<label for="mphb-country"><?php echo esc_html__( 'Country', 'motopress-hotel-booking' ); ?></label>
				<?php
				$countriesList = MPHB()->settings()->main()->getCountriesBundle()->getCountriesList();
				?>
				<select id="mphb-country" name="country">
					<option value=""><?php echo esc_html__( '— Select —', 'motopress-hotel-booking' ); ?></option>
					<?php
					if ( ! empty( $countriesList ) ) {
						foreach ( $countriesList as $code => $value ) {
							?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php echo $code == strtoupper( $customer->getCountry() ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $value ); ?></option>
							<?php
						}
					}
					?>
				</select>
			</p>
			<p class="mphb-customer-state">
				<label for="mphb-state"><?php echo esc_html__( 'State / County', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-state" name="state" type="text" value="<?php echo esc_attr( $customer->getState() ); ?>" />
			</p>
			<p class="mphb-customer-city">
				<label for="mphb-city"><?php echo esc_html__( 'City', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-city" name="city" type="text" value="<?php echo esc_attr( $customer->getCity() ); ?>" />
			</p>
			<p class="mphb-customer-zip">
				<label for="mphb-zip"><?php echo esc_html__( 'Postcode', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-zip" name="zip" type="text" value="<?php echo esc_attr( $customer->getZip() ); ?>" />
			</p>
		</div>
		<div class="mphb-account-change-password">
			<p>
				<strong><?php echo esc_html__( 'Change Password', 'motopress-hotel-booking' ); ?></strong>
			</p>
			<p class="mphb-account-old-password">
				<label for="mphb-old-password"><?php echo esc_html__( 'Old Password', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-old-password" name="old_password" type="password" />
			</p>
			<p class="mphb-account-new-password">
				<label for="mphb-new-password"><?php echo esc_html__( 'New Password', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-new-password" name="new_password" type="password" />
			</p>
			<p class="mphb-account-confirm-new-password">
				<label for="mphb-confirm-new-password"><?php echo esc_html__( 'Confirm New Password', 'motopress-hotel-booking' ); ?></label>
				<input id="mphb-confirm-new-password" name="confirm_new_password" type="password" />
			</p>
		</div>
		<p>
			<input type="submit" class="button" value="<?php echo esc_html__( 'Save Changes', 'motopress-hotel-booking' ); ?>" />
		</p>
	</form>
	<?php
} else {
	echo esc_html__( 'You are not allowed to access this page.', 'motopress-hotel-booking' );
}
