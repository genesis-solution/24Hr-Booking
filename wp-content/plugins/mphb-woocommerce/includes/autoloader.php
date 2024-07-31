<?php

namespace MPHBW;

class Autoloader {

	/**
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 *
	 * @var int
	 */
	private $prefixLength;

	/**
	 *
	 * @var string
	 */
	private $basePath;

	/**
	 *
	 * @var string
	 */
	private $classDirSeparator;
	private $customPathList = array();

	/**
	 *
	 * @param string $prefix Class prefix.
	 * @param string $basePath Path to classes directory.
	 */
	public function __construct( $prefix, $basePath ){

		$this->prefix		 = $prefix;
		$this->prefixLength	 = strlen( $this->prefix );
		$this->basePath		 = $basePath;

		$this->classDirSeparator = '\\'; // namespaces

		$this->setupCustomPathList();

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	private function setupCustomPathList(){
		$this->customPathList['Libraries\\EDD_Plugin_Updater\\EDD_Plugin_Updater'] = 'libraries/edd-plugin-updater/edd-plugin-updater.php';
	}

	/**
	 *
	 * @param string $class
	 */
	public function autoload( $class ){

		$class = ltrim( $class, '\\' );

		// does the class use the namespace prefix?
		if ( strncmp( $this->prefix, $class, $this->prefixLength ) !== 0 ) {
			// no, move to the next registered autoloader
			return false;
		}

		$relativeClass = substr( $class, $this->prefixLength );

		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $this->basePath . $this->convertClassToPath( $relativeClass );

		// if the file exists, require it
		if ( file_exists( $file ) ) {
			require_once $file;

			return $file;
		}
		return false;
	}

	/**
	 *
	 * @param string $class
	 * @return string Relative path to classfile.
	 */
	private function convertClassToPath( $class ){
		$path = '';

		if ( array_key_exists( $class, $this->customPathList ) ) {
			$path = $this->customPathList[$class];
		} else {
			$path = $this->defaultConvert( $class );
		}

		return $path;
	}

	private function defaultConvert( $class ){

		$filePath	 = $this->convertToFilePath( $class );
		$filePath	 = $this->lowerCamelCase( $filePath );
		$filePath	 = $this->replaceUnderscores( $filePath );

		return $filePath;
	}

	private function replaceUnderscores( $path ){
		return str_replace( '_', '-', $path );
	}

	private function lowerCamelCase( $class ){
		$class	 = preg_replace( '/([a-z])([A-Z])/', '$1-$2', $class );
		$class	 = preg_replace( '/([A-Z])([A-Z][a-z])/', '$1-$2', $class );
		$class	 = strtolower( $class );

		return $class;
	}

	private function convertToFilePath( $class ){

		$classFile = str_replace( $this->classDirSeparator, DIRECTORY_SEPARATOR, $class );

		$classFile = $classFile . '.php';

		return $classFile;
	}

}
