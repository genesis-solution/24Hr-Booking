<?php

namespace MPHBW\Settings;

class SettingsRegistry {

	private $main;
	private $license;

	public function __construct(){
		$this->main		 = new MainSettings();
		$this->license	 = new LicenseSettings();
	}

	/**
	 *
	 * @return MainSettings
	 */
	public function main(){
		return $this->main;
	}

	/**
	 *
	 * @return LicenseSettings
	 */
	public function license(){
		return $this->license;
	}

}
