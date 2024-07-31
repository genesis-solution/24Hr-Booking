<?php
/**
* The GetPostsParam class.
*
* Describes the 'get_terms' param type.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\App\Extensions\VisualComposer\Params;

use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionParamAbstract;

class GetPostsParam extends VisualComposerExtensionParamAbstract
{

    /**
     * Contains identifiers of all selected terms.
     *
     * @access protected
     * @var array
     */
    protected $selected_posts = array();

    /**
     * Returns an html of the param type field.
     *
     * @param array $settings
     * @param mixed $value
     * @access public
     * @return string
     */
    public function formField($settings, $value)
    {

        $posts = get_posts(array(
            'post_type' => $settings['post_type'],
            'post_status' => 'publish',
            'numberposts' => -1
        ));

        if(isset($settings['column'])) {
	        $column = $settings['column'];
		} else {
	        $column = 'ID';
		}

//		if(!isset($this->selected_posts[$settings['param_name']])) {
//			$this->selected_posts[$settings['param_name']] = array();
//		}
//
//        if ($value != "none" && !empty($value) && is_string($value)) {
//			$this->selected_posts[$settings['param_name']] = array_merge($this->selected_posts[$settings['param_name']], explode(',', $value));
//		}

	    if ( is_array($value) ) {
		    $inserted_vals = $value;
	    } else {
		    $inserted_vals = explode(',', $value);
	    }

        ob_start(); ?>

            <div class="milenia-param-wrapper">
                <select name="<?php echo esc_attr($settings['param_name']); ?>"
                        class="wpb_vc_param_value wpb-textinput <?php echo esc_attr($settings['param_name']); ?> <?php printf("%s_field", esc_attr($settings['type'])) ?>"
                        multiple style="min-height: 150px;">
                    <option value="">&nbsp;</option>
                    <?php if( is_array($posts) && count($posts) ) : ?>
						<?php foreach($posts as $item) : ?>
							<option <?php if(in_array($item->$column, $inserted_vals)) : ?> selected <?php endif; ?> value="<?php echo esc_attr($item->$column); ?>"><?php echo esc_html($item->post_title); ?></option>
						<?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

        <?php
        return ob_get_clean();
    }
}
?>
