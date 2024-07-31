<?php
/**
* The interface that describes a post repository functionality.
*
* @package WordPress
* @subpackage Milenia
* @since APOLA 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia') );
}

interface PostRepositoryInterface extends Countable {

	/**
     * Sets a single item id that will be in returned collection.
     *
     * @param int $id
     * @access public
     * @return array|WP_Post|null
     */
    public function find($id);

    /**
     * Sets a limit of posts in queried collection.
     *
     * @param int $limit
     * @access public
     * @return PostRepositoryInterface
     */
    public function limit($limit);

    /**
     * Sets an offset from start in queried collection.
     *
     * @param int $offset
     * @access public
     * @return PostRepositoryInterface
     */
    public function offset($offset);

    /**
     * Sets a column by which queried collection will be ordered.
     *
     * @param string $orderBy
     * @access public
     * @return PostRepositoryInterface
     */
    public function orderBy($orderBy);

    /**
     * Sets an ascending or descending order of queried collection.
     *
     * @param string $order
     * @access public
     * @return PostRepositoryInterface
     */
    public function order($order);

    /**
     * Sets categories from which posts will be fetched.
     *
     * @param string|array $categories
     * @param string|array $taxonomy
     * @access public
     * @return PostRepositoryInterface
     */
    public function fromCategories($categories, $taxonomy = 'category');

    /**
     * Sets a full array of arguments to query the collection.
     *
     * @param array $args
     * @access public
     * @return PostRepositoryInterface
     */
    public function args(array $args);

	/**
     * Sets ids of items from which collection will be constructed.
     *
     * @param array $ids
     * @access public
     * @return PostRepositoryInterface
     */
    public function in(array $ids);

	/**
     * Sets ids of items which won't be in final collection.
     *
     * @param array $ids
     * @access public
     * @return PostRepositoryInterface
     */
    public function out(array $ids);

    /**
     * Returns queried collection of posts.
     *
     * @access public
     * @return array
     */
    public function get();

	/**
     * Updates an item.
     *
     * @param WP_Post|array $item
     * @param string $meta_name
     * @param mixed $meta_value
     * @access public
     * @return bool
     */
    public function update($item, $meta_name, $meta_value);
}
?>
