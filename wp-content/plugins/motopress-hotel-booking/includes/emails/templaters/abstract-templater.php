<?php

namespace MPHB\Emails\Templaters;

abstract class AbstractTemplater {

	protected $tags = array();

	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'setupTags' ), 11 );
	}

	abstract public function setupTags();

	abstract public function replaceTag( $match );

	/**
	 * @param string $name
	 * @param string $description
	 * @param array  $atts
	 * @param bool   $atts['deprecated'] Optional. Set TRUE to mark tag as deprecated.
	 * @param string $atts['deprecated_title'] Optional.
	 */
	public function addTag( $name, $description, $atts = array() ) {

		$defaultAtts = array(
			'deprecated'       => false,
			'deprecated_title' => '',
		);

		$atts = array_merge( $defaultAtts, $atts );

		if ( ! empty( $name ) ) {

			$this->tags[ $name ] = array(
				'name'             => $name,
				'description'      => $description,
				'deprecated'       => $atts['deprecated'],
				'deprecated_title' => $atts['deprecated_title'],
				'inner_tags'       => isset( $atts['inner_tags'] ) ? $atts['inner_tags'] : array(),
			);
		}
	}

	/**
	 * @param string                 $content
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	public function replaceTags( $content ) {

		if ( ! empty( $this->tags ) ) {
			$content = preg_replace_callback( $this->_generateTagsFindString( $this->tags ), array( $this, 'replaceTag' ), $content );
		}

		return $content;
	}

	/**
	 * @param array $tags
	 * @return string
	 */
	protected function _generateTagsFindString( $tags ) {

		return '/%' . join( '%|%', wp_list_pluck( $tags, 'name' ) ) . '%/s';
	}

	/**
	 * @return string
	 */
	public function getTagsDescription() {

		$description  = '<details>';
		$description .= '<summary>' . __( 'Email Tags', 'motopress-hotel-booking' ) . '</summary>';

		$deprecatedSection = '';
		if ( ! empty( $this->tags ) ) {
			$description .= '<div class="mphb-email-tags-wrapper">';
			$description .= '<table class="striped mphb-email-tags">';
			foreach ( $this->tags as $tagDetails ) {
				$description   .= '<tr>';
				$tagDescription = sprintf( '<td>%2$s</td><td><em>%%%1$s%%</em></td>', $tagDetails['name'], $tagDetails['description'] );
				if ( $tagDetails['deprecated'] ) {
					$deprecatedTitle    = ! empty( $tagDetails['deprecated_title'] ) ? $tagDetails['deprecated_title'] : __( 'Deprecated.', 'motopress-hotel-booking' );
					$deprecatedSection .= '<span class="mphb-deprecated">';
					$deprecatedSection .= '<strong title="' . esc_attr( $deprecatedTitle ) . '">' . __( 'Deprecated.', 'motopress-hotel-booking' ) . '</strong> ';
					$deprecatedSection .= $tagDescription;
					$deprecatedSection .= '</span>';
				} else {
					$description .= $tagDescription;
				}
				$description .= '</tr>';
			}
			$description .= '</table>';
			$description .= '</div>';
			$description .= $deprecatedSection;
		} else {
			$description .= '<em>' . __( 'none', 'motopress-hotel-booking' ) . '</em>';
		}
		$description .= '</details>';

		return $description;
	}

	/**
	 * Retrieve names array of deprecated tags.
	 *
	 * @note this method works correct after plugins_loaded hook only
	 * @return array
	 */
	public function getDeprecatedTags() {

		$deprecatedTags     = array_filter(
			$this->tags,
			function( $tagDetails ) {
				return $tagDetails['deprecated'];
			}
		);
		$deprecatedTagNames = array_map(
			function( $tagDetails ) {
				return $tagDetails['name'];
			},
			$deprecatedTags
		);
		return $deprecatedTagNames;
	}

	/**
	 * @param string $tag
	 * @return bool
	 *
	 * @since 3.8.4
	 */
	public function hasTag( $tag ) {

		return array_key_exists( $tag, $this->tags );
	}
}
