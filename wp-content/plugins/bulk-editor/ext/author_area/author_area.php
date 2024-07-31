<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_AUTHOR_AREA extends WPBE_EXT {
	public $user_roles = array();
	public function __construct() {
		//'test_author'
		$this->user_roles = apply_filters('wpbe_author_area_roles',[]);//"test_author";
		
		if (!empty($this->user_roles)){
			add_filter('wpbe_apply_query_filter_data', array($this, 'add_query'));

			add_filter('wpbe_user_can_edit', array($this ,'user_can'), 10, 3);
		}
		
		//add_filter('wpbe_permit_special_roles', array($this ,'permit'), 10);
	}

	public function permit($roles) {
		$roles = array_merge($roles, $this->user_roles);
		return $roles;
	}
	public function add_query($args) {
		$user = wp_get_current_user();
		$match = array_intersect((array) $user->roles, $this->user_roles);
		if ( count($match)) {		
			$args['author'] = $user->ID;
		}		
		return $args;
	}
	public function user_can($visibility, $field_key, $site_editor_visibility) {
		$user = wp_get_current_user();
		if ($field_key == 'post_author') {
			return 0;
		}
		$match = array_intersect((array) $user->roles, $this->user_roles);
		if ( count($match) && $visibility) {		
			return $visibility;
		}else {
			return 0;
		}
		
	}
}

