<?php

namespace MPHB\Admin\MenuPages;

class LanguageMenuPage extends AbstractMenuPage {

	public function render() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Language Guide', 'motopress-hotel-booking' ); ?></h1>
			<h2><?php esc_html_e( 'Default language', 'motopress-hotel-booking' ); ?></h2>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p><?php printf( __( 'This plugin will display all system messages, labels, buttons in the language set in <em>General > Settings > Site Language</em>. If the plugin is not available in your language, you may <a href="%s">contribute your translation</a>.', 'motopress-hotel-booking' ), 'https://translate.getmotopress.com/' ); ?></p>
			<h2><?php esc_html_e( 'Custom translations and edits', 'motopress-hotel-booking' ); ?></h2>
			<p><?php esc_html_e( 'You may customize plugin translation by editing the needed texts or adding your translation following these steps:', 'motopress-hotel-booking' ); ?></p>
			<ol>
				<li><?php printf( esc_html__( 'Take the source file for your translations %s or needed translated locale.', 'motopress-hotel-booking' ), '<code>\motopress-hotel-booking\languages\motopress-hotel-booking.pot</code>' ); ?></li>
				<li><?php esc_html_e( 'Translate texts with any translation program like Poedit, Loco, Pootle etc.', 'motopress-hotel-booking' ); ?></li>
				<li><?php printf( esc_html__( 'Put created .mo file with your translations into the folder %s. Where {lang} is ISO-639 language code and {country} is ISO-3166 country code. Example: Brazilian Portuguese file would be called motopress-hotel-booking-pt_BR.mo.', 'motopress-hotel-booking' ), '<code>/wp-content/languages/motopress-hotel-booking/motopress-hotel-booking-{lang}_{country}.mo</code>' ); ?></li>
			</ol>
			<p></p>
			<h2><?php esc_html_e( 'Multilingual content', 'motopress-hotel-booking' ); ?></h2>
			<p><?php esc_html_e( 'If your site is multilingual, you may use additional plugins to translate your added content into multiple languages allowing the site visitors to switch them.', 'motopress-hotel-booking' ); ?></p>
		</div>
		<?php
	}

	public function onLoad() {
		// Do nothing.
	}

	protected function getMenuTitle() {
		return __( 'Language', 'motopress-hotel-booking' );
	}

	protected function getPageTitle() {
		return __( 'Language Guide', 'motopress-hotel-booking' );
	}

}
