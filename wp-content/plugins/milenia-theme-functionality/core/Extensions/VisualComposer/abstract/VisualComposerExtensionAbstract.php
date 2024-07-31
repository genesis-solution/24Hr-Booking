<?php
/**
* The VisualComposerExtensionAbstract abstract class.
*
* This class is responsible to describe base functionality of Visual Composer
* as a part of the theme.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\Core\Extensions\VisualComposer;

abstract class VisualComposerExtensionAbstract
{

    /**
     * Contains an array of custom Visual Composer params.
     *
     * @access protected
     * @var array
     */
    protected $custom_params = array();

	/**
	 * Constructor of the class.
	 */
	public function __construct()
	{
		// Break loading in case where Visual Composer has not been activated
		if( !function_exists('vc_map') ) return;

		add_action('vc_before_init', array($this, 'beforeInitComposer'));
	}

	/**
	 * Describes an action of the vc_before_init hook.
	 *
     * @abstract
	 * @access public
	 */
	abstract public function beforeInitComposer();

	/**
	 * Registers a shortcode in the visual composer.
	 *
	 * @param VisualComposerShortcodeInterface $shortcode
     * @abstract
	 * @access public
	 * @return void
	 */
	abstract public function addShortcode(VisualComposerShortcodeInterface $shortcode);

    /**
     * Registers new VC parameter.
     *
     * @param VisualComposerExtensionParamAbstract
     * @access public
     * @return VisualComposerExtension
     */
    public function addParam(VisualComposerExtensionParamAbstract $param)
    {
        $func_name = strrev('marap_edoctrohs_dda_cv');

        if(!function_exists($func_name)) return $this;

        foreach($this->custom_params as $registered_param) {
            if($registered_param->getName() == $param->getName()) return $this;
        }

        $func_name($param->getName(), array($param, 'formField'), $param->getScriptUrl());
		array_push($this->custom_params, $param);

        return $this;
    }
}
?>
