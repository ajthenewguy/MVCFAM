<?php namespace MVCFAM\App\View\Html;

use MVCFAM\App\View\Html\Tag;

/**
 * new Icon('bookmark')
 * <i class="icon-bookmark"></i>
 * Icon::bookmark()->white()
 * <i class="icon-bookmark icon-white"></i>
 */

/**
 * A classic Icon pattern
 */
class Icon extends Tag
{
	/**
	 * The Icon's tag
	 *
	 * @var string
	 */
	protected $element = 'i';

	/**
	 * Create a new icon
	 *
	 * @param string $icon The icon
	 */
	public function __construct($icon, $prefix = 'fa')
	{
		$this->class($prefix);
		$this->addClass($prefix.'-'.$icon);
	}

	/**
	 * Static alias for constructor
	 */
	public static function create($icon, $prefix = 'fa')
	{
		return new static($icon, $prefix);
	}

	/**
	 * Static alias for constructor
	 */
	public static function __callStatic($icon, $prefix = 'fa')
	{
		return new static($icon, $prefix);
	}

	/**
	 * Make the Icon white
	 */
	public function inverse()
	{
		$this->addClass('fa-inverse');
	}
}