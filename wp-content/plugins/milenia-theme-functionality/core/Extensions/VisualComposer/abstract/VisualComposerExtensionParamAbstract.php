<?php
/**
* The VisualComposerExtensionParamAbstract class that describes base functionality of a param type.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\Core\Extensions\VisualComposer;

abstract class VisualComposerExtensionParamAbstract
{

    /**
     * Contains the name of the param type.
     *
     * @access protected
     * @var null|string
     */
    protected $name;

    /**
     * Contains the script url of the param type.
     *
     * @access protected
     * @var null|string
     */
    protected $script_url;

    /**
     * Constructor of the class.
     *
     * @param string $name
     * @param string $script_url
     */
    public function __construct($name, $script_url = null)
    {
        $this->name = $name;
        $this->script_url = $script_url;
    }

    /**
     * Returns an html of the param type field.
     *
     * @param array $settings
     * @param mixed $value
     * @access public
     * @return string
     */
    abstract public function formField($settings, $value);

    /**
     * Returns name of the param type.
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns script url of the param type.
     *
     * @access public
     * @return string
     */
    public function getScriptUrl() {
        return $this->script_url;
    }
}
?>
