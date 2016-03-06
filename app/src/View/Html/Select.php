<?php namespace MVCFAM\App\View\Html;

use MVCFAM\App\View\Html\Element;
use MVCFAM\App\View\Html\Helpers;
use MVCFAM\App\View\Html\Tag;

/**
 * An input.
 */
class Select extends Tag
{
    /**
     * The tag element.
     *
     * @type string
     */
    protected $element = 'select';

    /**
     * Default element for nested children.
     *
     * @type string
     */
    protected $defaultChild = 'option';

    /**
     * Whether the element is self closing.
     *
     * @type boolean
     */
    protected $isSelfClosing = false;

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// CORE METHODS //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Create a new Select.
     *
     * @param string      $type       Its type
     * @param string|null $name       Its name
     * @param string|null $value      Its value
     * @param array       $attributes
     */
    public function __construct($name = null, $values = [], $selected_values = [], $attributes = [])
    {
        if (is_array($selected_values)) {
            $attributes['multiple'] = 'multiple';
        }
        if (! empty($values)) {
            $_values = [];
            foreach ($values as $key => $_value) {
                $selected = in_array($key, $selected_values);
                $_values[] = static::option($key, $_value, $selected);
            }
            $values = $_values;
        }

        $attributes['name'] = $name;

        $this->setTag('select', $values, $attributes);
    }

    /**
     * Create a new Select.
     *
     * @param string      $type       Its type
     * @param string|null $name       Its name
     * @param string|null $value      Its value
     * @param array       $attributes
     *
     * @return $this
     */
    public static function create($name = null, $values = [], $selected_values = [], $attributes = [])
    {
        return new static($name, $values, $selected_values, $attributes);
    }

    /**
     * Dynamically create a select option.
     *
     * @param string $value     The value attr
     * @param string $text      The text content
     * @param bool   $selected  Set to true to include selected attr
     *
     * @return $this
     */
    public static function option($value, $text, $selected = false)
    {
        $option = Element::option($text)->setValue($value);
        if ($selected) {
            $option->setAttribute('selected', true);
        }
        return $option;
    }
}