<?php
/**
* This class implements PostLikerInterface interface and provides simple
* interface to like/unlike posts independently of post type.
*
* @package WordPress
* @subpackage Milenia
* @since APOLA 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia') );
}

if(!class_exists('MileniaPostLiker') && interface_exists('PostLikerInterface')) {
    class MileniaPostLiker implements PostLikerInterface {

        protected $PostRepository;

        /**
         * Constructor of the class.
         *
         * @param PostRepositoryInterface $PostRepository
         */
        public function __construct(PostRepositoryInterface $PostRepository)
        {
            $this->PostRepository = $PostRepository;
        }

    	/**
         * Increments amount of likes of the post with specified id.
         *
         * @param int $item_id
         * @access public
         * @return bool
         */
        public function like($item_id)
        {
            $item = $this->PostRepository->find($item_id);

            if($item) {
                $item_likes = intval($this->getLikesCount($item_id));

                if($this->addToUserLiked($item_id)) {
                    if(boolval($this->PostRepository->update($item, 'milenia-item-likes-count', $item_likes + 1))) {
                        return true;
                    }
                    else {
                        $this->removeFromUserLiked($item_id);
                        return false;
                    }
                }
            }

            return false;
        }

        /**
         * Decrements amount of likes of the post with specified id.
         *
         * @param int $item_id
         * @access public
         * @return bool
         */
        public function unlike($item_id)
        {
            $item = $this->PostRepository->find($item_id);

            if($item) {
                $item_likes = intval($this->getLikesCount($item_id));

				if($item_likes <= 0 && $this->isLiked($item_id)) {
					$this->removeFromUserLiked($item_id);
					return $this->like($item_id);
				}


                if($this->removeFromUserLiked($item_id)) {
                    if(boolval($this->PostRepository->update($item, 'milenia-item-likes-count', $item_likes - 1))) {
                        return true;
                    }
                    else {
                        $this->addToUserLiked($item_id);
                        return false;
                    }
                }
            }

            return false;
        }

        /**
         * Returns true if post with specified id already has been liked.
         *
         * @param int $item_id
         * @access public
         * @return bool
         */
        public function isLiked($item_id)
        {
            return is_user_logged_in() ? $this->postLikedByWPUser($item_id) : $this->postLikedByNonWPUser($item_id);
        }

        /**
         * Returns the amount of likes of post with specified id.
         *
         * @param int $item_id
         * @access public
         * @return int
         */
        public function getLikesCount($item_id)
        {
            $item = $this->PostRepository->find($item_id);

            if($item && $item instanceof WP_Post) {
                return intval(get_post_meta($item->ID, 'milenia-item-likes-count', true));
            }
            elseif($item && is_array($item) && isset($item['milenia-item-likes-count'])) {
                return intval($item['milenia-item-likes-count']);
            }

            return null;
        }

        /**
         * Returns true if post with specified id already has been liked by WordPress user.
         *
         * @param int $item_id
         * @access public
         * @return bool
         */
        public function postLikedByWPUser($item_id)
        {
            $user_id = get_current_user_id();
            $user_liked_posts = get_user_meta($user_id, 'milenia-user-liked-posts', true);

            return is_array($user_liked_posts) && in_array($item_id, $user_liked_posts);
        }

		/**
         * Returns true if post with specified id already has been liked by non WordPress user.
         *
         * @param int $item_id
         * @access public
         * @return bool
         */
        public function postLikedByNonWPUser($item_id)
        {
            $client_ip = $this->getCurrentClientIP();
			$non_wp_user_likes = get_option('milenia-non-wp-user-likes', array());

			return is_array($non_wp_user_likes) && array_key_exists($client_ip, $non_wp_user_likes) && is_array($non_wp_user_likes[$client_ip]) && in_array($item_id, $non_wp_user_likes[$client_ip]);
        }

        /**
         * Adds item id to array of user liked posts.
         *
         * @param int $item_id
         * @access protected
         * @return bool
         */
        protected function addToUserLiked($item_id)
        {
			return is_user_logged_in() ? $this->addToWPUserLiked($item_id) : $this->addToNonWPUserLiked($item_id);
        }

        /**
         * Removes item id from array of user liked posts.
         *
         * @param int $item_id
         * @access protected
         * @return bool
         */
        protected function removeFromUserLiked($item_id)
        {
			return is_user_logged_in() ? $this->removeFromWPUserLiked($item_id) : $this->removeFromNonWPUserLiked($item_id);
        }

		/**
         * Adds item id to array of liked posts by WordPress user.
         *
         * @param int $item_id
         * @access protected
         * @return bool
         */
		protected function addToWPUserLiked($item_id)
		{
			$user_id = get_current_user_id();
			$user_liked_posts = get_user_meta($user_id, 'milenia-user-liked-posts', true);

			if(!is_array($user_liked_posts)) {
				$user_liked_posts = array();
			}

			if(!in_array($item_id, $user_liked_posts)) array_push($user_liked_posts, $item_id);

			return update_user_meta($user_id, 'milenia-user-liked-posts', $user_liked_posts);
		}

		/**
         * Adds item id to array of liked posts by non WordPress user.
         *
         * @param int $item_id
         * @access protected
         * @return bool
         */
		protected function addToNonWPUserLiked($item_id)
		{
			$client_ip = $this->getCurrentClientIP();
			$non_wp_user_likes = get_option('milenia-non-wp-user-likes', array());

			if(!is_array($non_wp_user_likes)) {
				$non_wp_user_likes = array();
			}

			if(!array_key_exists($client_ip, $non_wp_user_likes)) {
				$non_wp_user_likes[$client_ip] = array();
			}

			if(!in_array($item_id, $non_wp_user_likes[$client_ip])) {
				array_push($non_wp_user_likes[$client_ip], $item_id);
			}

			return update_option('milenia-non-wp-user-likes', $non_wp_user_likes);
		}

		/**
         * Removes item id from array of liked posts by WordPress user.
         *
         * @param int $item_id
         * @access protected
         * @return bool
         */
		protected function removeFromWPUserLiked($item_id)
		{
			$user_id = get_current_user_id();
			$user_liked_posts = get_user_meta($user_id, 'milenia-user-liked-posts', true);

			if(is_array($user_liked_posts) && count($user_liked_posts)) {
				foreach($user_liked_posts as $index => $post_id) {
					if($post_id == $item_id) unset($user_liked_posts[$index]);
				}
			}

			return update_user_meta($user_id, 'milenia-user-liked-posts', $user_liked_posts);
		}

		/**
         * Removes item id from array of liked posts by non WordPress user.
         *
         * @param int $item_id
         * @access protected
         * @return bool
         */
		protected function removeFromNonWPUserLiked($item_id)
		{
			$client_ip = $this->getCurrentClientIP();
			$non_wp_user_likes = get_option('milenia-non-wp-user-likes', array());

			if(is_array($non_wp_user_likes) && count($non_wp_user_likes) && array_key_exists($client_ip, $non_wp_user_likes)) {
				if(is_array($non_wp_user_likes[$client_ip]) && count($non_wp_user_likes[$client_ip])) {

					foreach($non_wp_user_likes[$client_ip] as $index => $post_id) {
						if($item_id == $post_id) {
							unset($non_wp_user_likes[$client_ip][$index]);
							break;
						}
					}
				}
			}

			return update_option('milenia-non-wp-user-likes', $non_wp_user_likes);
		}

		/**
		 * Returns IP address of the current client.
		 *
		 * @access protected
		 * @return string
		 */
		protected function getCurrentClientIP()
		{
			if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
				$client_ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else {
				$client_ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
			}

			$client_ip = filter_var($client_ip, FILTER_VALIDATE_IP);
			$client_ip = ($client_ip === false) ? '0.0.0.0' : $client_ip;

			return $client_ip;
		}
    }
}


?>
