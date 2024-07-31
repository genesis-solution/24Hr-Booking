<?php
/**
* The PostLikerInterface interace. If you need your own functionality of post
* like/unlike methods, just implement this interface.
*
* @package WordPress
* @subpackage Milenia
* @since APOLA 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia') );
}

interface PostLikerInterface {

    /**
     * Constructor of the class.
     *
     * @param PostRepositoryInterface $PostRepository
     */
    public function __construct(PostRepositoryInterface $PostRepository);

	/**
     * Increments amount of likes of the post with specified id.
     *
     * @param int $item_id
     * @access public
     * @return bool
     */
    public function like($item_id);

    /**
     * Decrements amount of likes of the post with specified id.
     *
     * @param int $item_id
     * @access public
     * @return bool
     */
    public function unlike($item_id);

    /**
     * Returns true if post with specified id already has been liked.
     *
     * @param int $item_id
     * @access public
     * @return bool
     */
    public function isLiked($item_id);

    /**
     * Returns the amount of likes of post with specified id.
     *
     * @param int $item_id
     * @access public
     * @return int
     */
    public function getLikesCount($item_id);
}
?>
