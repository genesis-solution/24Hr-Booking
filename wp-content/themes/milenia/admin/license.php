<?php
class Milenia_lic {
	public $plugin_file=__FILE__;
	public $responseObj;
	public $licenseMessage;
	public $showMessage=false;
	public $slug="Milenia";
	function __construct() {
		add_action( 'admin_print_styles', [ $this, 'SetAdminStyle' ] );
		$licenseKey=get_option("Milenia_lic_Key","");
		$liceEmail=get_option( "Milenia_lic_email","");
		if(MileniaBase::CheckWPPlugin($licenseKey,$liceEmail,$this->licenseMessage,$this->responseObj, get_template_directory()."/style.css")){
			add_action( 'admin_menu', [$this,'ActiveAdminMenu']);
			add_action( 'admin_post_Milenia_el_deactivate_license', [ $this, 'action_deactivate_license' ] );

		}else{
			if(!empty($licenseKey) && !empty($this->licenseMessage)){
				$this->showMessage=true;
			}
			update_option("Milenia_lic_Key","") || add_option("Milenia_lic_Key","");
			add_action( 'admin_post_Milenia_el_activate_license', [ $this, 'action_activate_license' ] );
			add_action( 'admin_menu', [$this,'InactiveMenu']);
		}
	}
	function SetAdminStyle() {
		wp_register_style( "MileniaLic", get_theme_file_uri("assets/css/lic_style.css"),10);
		wp_enqueue_style( "MileniaLic" );
	}
	function ActiveAdminMenu(){
		add_theme_page ( "Milenia", "Milenia", 'activate_plugins', $this->slug, [$this,"Activated"]);
	}
	function InactiveMenu() {
		add_theme_page( "Milenia", "Milenia", 'activate_plugins', $this->slug,  [$this,"LicenseForm"]);
	}
	function action_activate_license(){
		check_admin_referer( 'el-license' );
		$licenseKey=!empty($_POST['el_license_key'])?$_POST['el_license_key']:"";
		$licenseEmail=!empty($_POST['el_license_email'])?$_POST['el_license_email']:"";
		update_option("Milenia_lic_Key",$licenseKey) || add_option("Milenia_lic_Key",$licenseKey);
		update_option("Milenia_lic_email",$licenseEmail) || add_option("Milenia_lic_email",$licenseEmail);

		wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
	}
	function action_deactivate_license() {
		check_admin_referer( 'el-license' );
		if(MileniaBase::RemoveLicenseKey(__FILE__,$message)){
			update_option("Milenia_lic_Key","") || add_option("Milenia_lic_Key","");
		}
		wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
	}
	function Activated(){


		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="Milenia_el_deactivate_license"/>
			<div class="el-license-container">
				<h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php _e("Milenia License Info","milenia");?> </h3>
				<hr>
				<ul class="el-license-info">
					<li>
						<div>
							<span class="el-license-info-title"><?php _e("Status","milenia");?></span>

							<?php if ( $this->responseObj->is_valid ) : ?>
								<span class="el-license-valid"><?php _e("Valid","milenia");?></span>
							<?php else : ?>
								<span class="el-license-valid"><?php _e("Invalid","milenia");?></span>
							<?php endif; ?>
						</div>
					</li>

					<li>
						<div>
							<span class="el-license-info-title"><?php _e("License Type","milenia");?></span>
							<?php echo $this->responseObj->license_title; ?>
						</div>
					</li>

					<li>
						<div>
							<span class="el-license-info-title"><?php _e("License Expired on","milenia");?></span>
							<?php echo $this->responseObj->expire_date; ?>
						</div>
					</li>

					<li>
						<div>
							<span class="el-license-info-title"><?php _e("Support Expired on","milenia");?></span>
							<?php echo $this->responseObj->support_end; ?>
						</div>
					</li>
					<li>
						<div>
							<span class="el-license-info-title"><?php _e("Your License Key","milenia");?></span>
							<span class="el-license-key"><?php echo esc_attr( substr($this->responseObj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->responseObj->license_key,-9) ); ?></span>
						</div>
					</li>
				</ul>
				<div class="el-license-active-btn">
					<?php wp_nonce_field( 'el-license' ); ?>
					<?php submit_button('Deactivate'); ?>
				</div>
			</div>
		</form>
		<?php
	}

	function LicenseForm() {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="Milenia_el_activate_license"/>
			<div class="el-license-container">
				<h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php _e("Milenia Theme Licensing","milenia");?></h3>
				<hr>
				<?php
				if(!empty($this->showMessage) && !empty($this->licenseMessage)){
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo $this->licenseMessage; ?></p>
					</div>
					<?php
				}
				?>
				<p><?php _e("Enter your license key here, to activate the product, and get full feature updates and premium support. <br><br>Log into your Envato Market account
    Hover the mouse over your username at the top of the screen.<br>
    Click ‘Downloads’ from the drop down menu.`<br>
    Click ‘License certificate & purchase code’ (available as PDF or text file). or <a href='https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-' target='_blank'>Click Here</a> to watch video<br>","milenia");?></p>

				<div class="el-license-field">
					<label for="el_license_key"><?php _e("License code","milenia");?></label>
					<input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
				</div>
				<div class="el-license-field">
					<label for="el_license_key"><?php _e("Email Address","milenia");?></label>
					<?php
					$purchaseEmail   = get_option( "Milenia_lic_email", get_bloginfo( 'admin_email' ));
					?>
					<input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo $purchaseEmail; ?>" placeholder="" required="required">
					<div><small><?php _e("We will send update news of this product by this email address, don't worry, we hate spam","milenia");?></small></div>
				</div>
				<div class="el-license-active-btn">
					<?php wp_nonce_field( 'el-license' ); ?>
					<?php submit_button('Activate'); ?>
				</div>
			</div>
		</form>
		<?php
	}
}

new Milenia_lic();