<?php

/**
 *
 * @param string $version version to compare with wp version
 * @param string $operator Optional. Possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively. Default =.
  This parameter is case-sensitive, values should be lowercase.
 * @return bool
 */
function mphbw_is_wp_version( $version, $operator = '=' ){
	global $wp_version;
	return version_compare( $wp_version, $version, $operator );
}

/**
 * Check is plugin active.
 *
 * @param string $pluginSubDirSlashFile
 * @return bool
 */
function mphbw_is_plugin_active( $pluginSubDirSlashFile ){
	if ( !function_exists( 'is_plugin_active' ) ) {
		/**
		 * Detect plugin. For use on Front End only.
		 */
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	return is_plugin_active( $pluginSubDirSlashFile );
}

/**
 * Gets an array of tags to replace in a template.
 *
 * @since 1.0.6
 *
 * @return array
 */
function mphbw_get_tags() {
	return ['booking_id', 'reserved_accommodation_names', 'check_in_date', 'check_out_date'];
}

/**
 *
 * @since 1.0.6
 *
 * @return string
 */
function mphbw_generate_tags_find_string() {
	$tags = mphbw_get_tags();

	return '/%' . join( '%|%', $tags ) . '%/s';
}

/**
 *
 * @return \MPHBW\Plugin
 */
function MPHBW(){
	return \MPHBW\Plugin::getInstance();
}
