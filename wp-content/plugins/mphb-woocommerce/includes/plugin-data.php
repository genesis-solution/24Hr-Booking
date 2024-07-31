<?php

namespace MPHBW;

class PluginData {

	private $pluginFile;
	private $pluginDir;
	private $classesBasePath;
	private $pluginDirUrl;
	private $pluginURI;
	private $slug;
	private $author;
	private $name;
	private $version;

	public function __construct( $pluginFile ){
		$this->pluginFile		 = $pluginFile;
		$this->pluginDir		 = plugin_dir_path( $pluginFile );
		$this->classesBasePath	 = trailingslashit( $this->getPluginPath( 'includes' ) );
		$this->pluginDirUrl		 = plugin_dir_url( $pluginFile );
		$this->slug				 = basename( $this->pluginDir );

		$pluginData		 = $this->getPluginData();
		$this->author	 = isset( $pluginData['Author'] ) ? $pluginData['Author'] : '';
		$this->name		 = isset( $pluginData['Name'] ) ? $pluginData['Name'] : '';
		$this->version	 = isset( $pluginData['Version'] ) ? $pluginData['Version'] : '';
		$this->pluginURI = isset( $pluginData['PluginURI'] ) ? $pluginData['PluginURI'] : '';
	}

	/**
	 *
	 * @return array
	 */
	private function getPluginData(){
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		return get_plugin_data( $this->pluginFile, false, false );
	}

	/**
	 *
	 * @return string
	 */
	public function getPluginFile(){
		return $this->pluginFile;
	}

	/**
	 * Retrieve full path for the relative to plugin root path.
	 *
	 * @param string $relativePath
	 * @return string
	 */
	public function getPluginPath( $relativePath = '' ){
		return $this->pluginDir . $relativePath;
	}

	/**
	 *
	 * @param string $relativePath
	 * @return string
	 */
	public function getPluginUrl( $relativePath = '' ){
		return $this->pluginDirUrl . $relativePath;
	}

	/**
	 *
	 * @return string
	 */
	public function getPluginDir(){
		return $this->pluginDir;
	}

	/**
	 *
	 * @return string
	 */
	public function getClassesBasePath(){
		return $this->classesBasePath;
	}

	/**
	 *
	 * @return string
	 */
	public function getPluginDirUrl(){
		return $this->pluginDirUrl;
	}

	/**
	 *
	 * @return string
	 */
	public function getSlug(){
		return $this->slug;
	}

	/**
	 *
	 * @return string
	 */
	public function getAuthor(){
		return $this->author;
	}

	/**
	 *
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 *
	 * @return string
	 */
	public function getVersion(){
		return $this->version;
	}

	/**
	 *
	 *
	 * @return string
	 */
	public function getPluginURI(){
		return $this->pluginURI;
	}

}
