<?php
/**
* The GetTermsParam class.
*
* Describes the 'get_terms' param type.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/
namespace Milenia\App\Extensions\VisualComposer\Params;

use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionParamAbstract;

class GetTermsParam extends VisualComposerExtensionParamAbstract
{

    /**
     * Contains identifiers of all selected terms.
     *
     * @access protected
     * @var array
     */
    protected $selected_terms = array();

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
        $terms = get_terms(array(
            'taxonomy' => $settings['term'],
            'hide_empty' => 0,
            'parent' => 0
        ));

        if (isset($settings['column']) ) {
	        $column = $settings['column'];
		} else {
	        $column = 'term_id';
		}

	    if ( is_array($value) ) {
		    $inserted_vals = $value;
		} else {
		    $inserted_vals = explode(',', $value);
		}

        ob_start();?>

            <div class="apo-param-wrapper">
                <select name="<?php echo esc_attr($settings['param_name']); ?>"
                        class="wpb_vc_param_value wpb-textinput <?php echo esc_attr($settings['param_name']); ?> <?php printf("%s_field", esc_attr($settings['type'])) ?>"
                        multiple style="min-height: 200px;">
					<option value="">&nbsp;</option>
                    <?php if(is_array($terms) && count($terms)) : ?>
						<?php foreach( $terms as $item ) : ?>
							<option <?php if(in_array($item->$column, $inserted_vals)) : ?> selected <?php endif; ?> value="<?php echo esc_attr($item->$column); ?>"><?php echo esc_html($item->name); ?></option>
						<?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

        <?php
        return ob_get_clean();
    }
}
?>
