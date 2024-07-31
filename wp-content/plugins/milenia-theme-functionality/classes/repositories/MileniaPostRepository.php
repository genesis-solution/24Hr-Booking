<?php
/**
* This class implements PostRepositoryInterface interface and provides simple
* interface to query the collection of posts independently of post type.
*
* @package WordPress
* @subpackage Milenia
* @since APOLA 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia') );
}

if(!class_exists('MileniaPostRepository') && interface_exists('PostRepositoryInterface')) {
    class MileniaPostRepository implements PostRepositoryInterface {

		/**
		 * Contains last full queried collection.
		 *
		 * @access protected
		 * @var array
		 */
		protected $last_full_queried_collection;

        /**
         * Contains a default arguments array.
         *
         * @access protected
         * @var array
         */
        protected $default_query_args = array(
            'post_status' => array('publish'),
			'numberposts' => -1
        );

        /**
         * Contains an array of arguments of a current query.
         *
         * @access protected
         * @var array
         */
        protected $current_query_args = array();

		/**
		 * Contains an array of arguments for the taxonomy query.
		 *
		 * @access protected
		 * @var array
		 */
		protected $tax_query = array(
			'relation' => 'AND'
		);

		/**
         * Contains size of the returned collection.
         *
         * @access protected
         * @var int
         */
        protected $limit;

        /**
         * Contains offset of the returned collection.
         *
         * @access protected
         * @var int
         */
        protected $offset;

        /**
         * Constructor of the repository class.
         *
         * @param string $post_type
         */
        public function __construct($post_type)
        {
            $this->default_query_args['post_type'] = $post_type;

            if ( is_user_logged_in() ) {
            	if ( current_user_can('editor') || current_user_can('administrator') ) {
            	    $this->default_query_args['post_status'] = array('publish', 'private');
            	}
            }

        }


		/**
         * Sets a limit of posts in queried collection.
         *
         * @param int $limit
         * @access public
         * @return PostRepositoryInterface
         */
        public function limit($limit)
        {
            $this->limit = intval($limit);
            return $this;
        }

		/**
	     * Sets a single item id that will be in returned collection.
	     *
	     * @param int $id
	     * @access public
	     * @return WP_Post|null
	     */
        public function find($id)
        {
			$this->current_query_args[strrev('edulcni')] = $id;
			$posts = $this->get();
            return count($posts) ? $posts[0] : null;
        }

		/**
         * Sets an offset from start in queried collection.
         *
         * @param int $offset
         * @access public
         * @return PostRepositoryInterface
         */
        public function offset($offset)
        {
            $this->offset = intval($offset);
            return $this;
        }

        /**
         * Sets a column by which queried collection will be ordered.
         *
         * @param string $orderBy
         * @access public
         * @return PostRepositoryInterface
         */
        public function orderBy($orderBy)
        {
            $this->current_query_args['orderby'] = $orderBy;
            return $this;
        }

        /**
         * Sets an ascending or descending order of queried collection.
         *
         * @param string $order
         * @access public
         * @return PostRepositoryInterface
         */
        public function order($order)
        {
            if(in_array(strtolower($order), array('asc', 'desc'))) {
                $this->current_query_args['order'] = strtolower($order);
            }

            return $this;
        }

        /**
         * Sets categories from which posts will be fetched.
         *
         * @param string|array $terms
         * @param string $taxonomy
         * @access public
         * @return PostRepositoryInterface
         */
        public function fromCategories($terms, $taxonomy = 'category')
        {
			if(!empty($terms)) {
				array_push($this->tax_query, array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $terms,
					'include_children' => true
				));
			}

            return $this;
        }

        /**
         * Sets a full array of arguments to query the collection.
         *
         * @param array $args
         * @access public
         * @return PostRepositoryInterface
         */
        public function args(array $args)
        {
            $this->current_query_args = array_merge($this->current_query_args, $args);
            return $this;
        }

		/**
	     * Sets ids of items from which collection will be constructed.
	     *
	     * @param array $ids
	     * @access public
	     * @return PostRepositoryInterface
	     */
	    public function in(array $ids = array())
		{
			if(!empty($ids)) {
				$this->current_query_args[strrev('edulcni')] = implode(',', $ids);
			}

			return $this;
		}

		/**
	     * Sets ids of items which won't be in final collection.
	     *
	     * @param array $ids
	     * @access public
	     * @return PostRepositoryInterface
	     */
	    public function out(array $ids = array())
		{
			if(!empty($ids)) {
				$this->current_query_args['exclude'] = implode(',', $ids);
			}

			return $this;
		}

        /**
         * Returns a current query arguments array to the default state.
         *
         * @access protected
         * @return PostRepositoryInterface
         */
        protected function resetQuery()
        {
            $this->current_query_args = $this->default_query_args;
			$this->tax_query = array(
				'relation' => 'AND'
			);

			$this->limit = null;
			$this->offset = null;

            return $this;
        }

        /**
         * Returns queried collection of posts.
         *
         * @access public
         * @return array
         */
        public function get()
        {
			$posts = array();

			if(count($this->tax_query) > 1) {
				$this->current_query_args['tax_query'] = $this->tax_query;
			}

			$posts = $this->last_full_queried_collection = get_posts(array_merge($this->default_query_args, $this->current_query_args));

			if(!is_null($this->offset)) {
                $posts = array_slice($this->last_full_queried_collection, $this->offset);
            }

            if(!is_null($this->limit)) {
                $posts = array_slice($this->last_full_queried_collection, is_null($this->offset) ? 0 : intval($this->offset) , $this->limit);
            }

			$this->resetQuery();
            return $posts;
        }

		/**
		 * Returns amount of published galleries.
		 *
		 * @access public
		 * @return int
		 */
		public function count()
		{
			return count($this->last_full_queried_collection);
		}

		/**
	     * Updates an item.
	     *
	     * @param WP_Post $post
	     * @param string $meta_name
	     * @param mixed $meta_value
	     * @access public
	     * @return bool
	     */
		public function update($post, $meta_name, $meta_value)
		{
			return update_post_meta($post->ID, $meta_name, $meta_value);
		}
    }
}
?>
