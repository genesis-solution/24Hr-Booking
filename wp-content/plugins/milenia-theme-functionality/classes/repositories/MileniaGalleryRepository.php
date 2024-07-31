<?php
/**
* This class implements PostRepositoryInterface interface and provides simple
* interface to query the collection of galleries.
*
* @package WordPress
* @subpackage Milenia
* @since APOLA 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia') );
}

if(!class_exists('MileniaGalleryRepository') && interface_exists('PostRepositoryInterface')) {
    class MileniaGalleryRepository implements PostRepositoryInterface
    {

		/**
		 * Contains last full queried collection.
		 *
		 * @access protected
		 * @var array
		 */
		protected $last_full_queried_collection;

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
         * Contains order of the returned collection.
         *
         * @access protected
         * @var int
         */
        protected $order;

		/**
         * Contains a property name that defines an order of returned collection.
         *
         * @access protected
         * @var string
         */
        protected $orderBy;

		/**
         * Contains an id of single item that will be in returned collection.
         *
         * @access protected
         * @var int
         */
        protected $seeking_ID;

        /**
         * Contains default query arguments.
         *
         * @access protected
         * @var array
         */
        protected $default_query_args = array(
            'post_type' => 'milenia-galleries',
            'numberposts' => -1,
            'post_status' => 'publish'
        );

        /**
         * Contains current query arguments.
         *
         * @access protected
         * @var array
         */
        protected $current_query_args = array();

		/**
		 * Constructor of the class.
		 *
		 * @param string $post_type
		 */
		public function __construct($post_type = 'milenia-galleries')
		{
			$this->default_query_args['post_type'] = $post_type;
		}

		/**
         * Returns a current query arguments array to the default state.
         *
         * @access protected
         * @return PostRepositoryInterface
         */
        protected function resetQuery()
        {
            $this->current_query_args = array();
            $this->limit = null;
            $this->offset = null;
			$this->order = null;
			$this->orderBy = null;
			$this->seeking_ID = null;

			return $this;
        }

        /**
         * Returns queried collection of galleries images.
         *
         * @access public
         * @return array
         */
        public function get()
        {
            $posts = get_posts(array_merge($this->default_query_args, $this->current_query_args));
            return $this->mapToImages($posts);
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
         * Sets categories from which items will be fetched.
         *
         * @param string|array $terms
         * @param string $taxonomy
         * @access public
         * @return PostRepositoryInterface
         */
        public function fromCategories($terms, $taxonomy = 'milenia-gallery-categories')
        {
			if(!empty($terms)) {
	            $this->current_query_args['tax_query'] = array(
	                array(
	                    'taxonomy' => $taxonomy,
	                    'field' => 'slug',
	                    'terms' => explode(',', $terms),
	                    'include_children' => false
	                )
	            );

			}

            return $this;
        }

		/**
         * Sets a limit of items in queried collection.
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
	     * @return array|null
	     */
        public function find($id)
        {
            $this->seeking_ID = intval($id);
			$items = $this->get();
            return count($items) ? $items[0] : null;
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
         * Sets an ascending or descending order of queried collection.
         *
         * @param string $order
         * @access public
         * @return PostRepositoryInterface
         */
        public function order($order)
        {
			if(in_array(strtolower($order), array('desc', 'asc'))) {
				$this->order = strtolower($order);
			}

            return $this;
        }

		/**
         * Sets a property by which queried collection will be ordered.
         *
         * @param string $orderBy
         * @access public
         * @return PostRepositoryInterface
         */
        public function orderBy($orderBy)
        {
			if(in_array(strtolower($orderBy), array('image-title', 'image-sub-title', 'image-description'))) {
				$this->orderBy = strtolower($orderBy);
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
			if(isset($args['numberposts'])) {
				$this->limit = intval($args['numberposts']);
				unset($args['numberposts']);
			}

			if(isset($args['offset'])) {
				$this->offset = intval($args['offset']);
				unset($args['offset']);
			}

			if(isset($args['order']) && in_array(strtolower($args['order']), array('asc', 'desc'))) {
				$this->order = strtolower($args['order']);
				unset($args['order']);
			}

			if(isset($args['orderby']) && in_array(strtolower($args['orderby']), array('image-title', 'image-sub-title', 'image-description'))) {
				$this->orderBy = strtolower($args['orderby']);
				unset($args['orderby']);
			}

			$this->current_query_args = array_merge($this->current_query_args, $args);
		}

        /**
         * Converts from gallery posts array to array of gallery images.
         *
         * @param array $posts
         * @access protected
         * @return array
         */
        protected function mapToImages(array $posts)
        {
            global $post;
            $images = array();

            if(count($posts)) {
                foreach($posts as $post) {
                    setup_postdata($post);

                    $gallery_builder = get_post_meta(get_the_ID(), 'milenia_gallery_builder', true);

                    if(isset($gallery_builder['sliders']) && isset($gallery_builder['sliders']['slides']) && is_array($gallery_builder['sliders']['slides'])) {
						foreach($gallery_builder['sliders']['slides'] as $image) {
							if(!is_null($this->seeking_ID) && $image['attach_id'] != $this->seeking_ID) continue;

							$image['parent_gallery_id'] = $post->ID;
							array_push($images, $image);
						}

//                         $images = array_merge($images, $gallery_builder['sliders']['slides']);
                    }
                }

                wp_reset_postdata();
            }

			$this->last_full_queried_collection = $images;

			if($this->order == 'desc') {
				$images = array_reverse($images);
			}

            if(!is_null($this->offset)) {
                $images = array_slice($images, $this->offset);
            }

            if(!is_null($this->limit)) {
                $images = array_slice($images, 0, $this->limit);
            }

            $this->resetQuery();
            return $images;
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
	     * @param array $item
	     * @param string $meta_name
	     * @param mixed $meta_value
	     * @access public
	     * @return bool
	     */
		public function update($item, $meta_name, $meta_value)
		{
			if(!isset($item['parent_gallery_id'])) return false;

			$gallery_builder = get_post_meta(intval($item['parent_gallery_id']), 'milenia_gallery_builder', true);

			if(is_array($gallery_builder)) {
				if(is_array($gallery_builder) && isset($gallery_builder['sliders']) && isset($gallery_builder['sliders']['slides']) && is_array($gallery_builder['sliders']['slides']) && count($gallery_builder['sliders']['slides'])) {
					foreach($gallery_builder['sliders']['slides'] as $index => &$gallery_builder_item) {
						if($gallery_builder_item['attach_id'] === $item['attach_id']) {
							$gallery_builder_item[$meta_name] = $meta_value;
							return update_post_meta(intval($item['parent_gallery_id']), 'milenia_gallery_builder', $gallery_builder);
						}
					}
				}
			}

			return false;
		}
    }
}
?>
