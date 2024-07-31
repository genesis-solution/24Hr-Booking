<?php

namespace MPHB\Payments\Gateways;

interface GatewayInterface {

	public function register( GatewayManager $gatewayManager );
	public function getId();
	public function getTitle();
	public function getAdminTitle();
	public function getAdminDescription();
	/** @since 3.6.1 */
	public function getInstructions();
	public function registerOptionsFields( &$subTab );
	public function isEnabled();
	public function isActive();
	public function isShowOptions();
}
