<?php

namespace MPHB\Admin;

abstract class AdminPage {

	/**
	 * Custom actions that output just after the page heading
	 *
	 * @var array [string "label", string "url", string "atts", string/html "after"]
	 */
	protected $titleActions = array();

	public function __construct() {
		add_action( 'admin_footer', array( $this, 'addTitleActionsScript' ) );
	}

	public function addTitleAction( $label, $url, $options = array() ) {
		$action = array(
			'label' => $label,
			'url'   => $url,
			'class' => ( isset( $options['class'] ) ? $options['class'] : '' ),
			'after' => ( isset( $options['after'] ) ? $options['after'] : '' ),
		);

		$action['class'] = trim( 'page-title-action ' . $action['class'] );

		if ( ! empty( $action['after'] ) ) {
			// Add space between action button and text
			$action['after'] = ' ' . $action['after'];
		}

		$this->titleActions[] = $action;
	}

	public function addTitleActionsScript() {
		if ( $this->isCurrentPage() && ! empty( $this->titleActions ) ) {
			$actions = array();

			foreach ( $this->titleActions as $action ) {
				$actions[] = '<a href="' . esc_url( $action['url'] ) . '" class="' . esc_attr( $action['class'] ) . '">' . esc_html( $action['label'] ) . '</a>' . $action['after'];
			}

			?>
			<script type="text/javascript">
				jQuery( function() {
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					var actions = ['<?php echo join( "', '", $actions ); ?>'];
					var $heading = jQuery( '#wpbody-content > .wrap > .wp-heading-inline' );

					actions.forEach( function( action ) {
						$heading.after( action );
					});
				} );
			</script>
			<?php
		}
	}

}
