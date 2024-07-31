<?php

namespace MPHB\Admin\EditCPTPages;

class CouponEditCPTPage extends EditCPTPage {

	protected function addActions() {
		parent::addActions();
		add_filter( 'enter_title_here', array( $this, 'changeTitlePlaceholder' ) );
	}

	public function changeTitlePlaceholder( $title ) {
		if ( $this->isCurrentPage() ) {
			$title = __( 'Coupon code', 'motopress-hotel-booking' );
		}
		return $title;
	}
}
