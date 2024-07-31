<?php
/**
* The main theme interface that describes functionality of configurable entity.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}


interface LayoutInterface
{
    /**
     * Returns true in case the page has sidebar.
     *
     * @access public
     * @return bool
     */
    public function hasSidebar();

    /**
     * Returns true in case the page has footer.
     *
     * @access public
     * @return bool
     */
    public function hasFooter();

    /**
     * Returns sidebar state.
     *
     * @access public
     * @return string
     */
    public function getSidebarState();

	/**
	 * Returns the current page sidebar.
	 *
	 * @access public
	 * @return string
	 */
	public function getSidebar();

    /**
     * Returns array of vertical padding of the content area.
     *
     * @access public
     * @return array
     */
    public function verticalContentPadding();

    /**
     * Returns main layout classes.
     *
     * @access public
     * @return string
     */
    public function getMainLayoutClasses();


    /**
     * Returns type of the current page header.
     *
     * @access public
     * @return string
     */
    public function getHeaderType();

    /**
     * Returns an array of items that are in current page header.
     *
     * @access public
     * @return string
     */
    public function getHeaderItems();

	/**
     * Returns the breadcrumb section settings.
     *
     * @access public
     * @return array|null
     */
    public function getBreadcrumb();

	/**
	 * Returns the footer data.
	 *
	 * @access public
	 * @return array
	 */
	public function getFooter();

	/**
     * Returns the current page type.
     *
     * @access public
     * @return string|null
     */
    public function getPageType();

	/**
	 * Returns true in case the page is full width.
	 *
	 * @access public
	 * @return boolean
	 */
	public function isFullWidth();
}

?>
