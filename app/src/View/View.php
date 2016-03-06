<?php namespace MVCFAM\App\View;

use MVCFAM\App\Object;

class View extends Object {
	
	public $file;

	protected $is_json;

	protected $file_extensions = array('.php', '.json');

	protected $file_extension;

	private $exists = false;

	private $vars;


	public function __construct($files = array(), $vars = array()) {
		$this->setVars($vars);
		foreach($files as $file) {
			if(!isset($this->file)) {
				foreach($this->file_extensions as $file_extension) {
					if(false === strpos($file, $file_extension)) {
						$file .= $file_extension;
					}
					if(file_exists(APP_VIEW_PATH.$file)) {
						$this->exists = true;
						$this->file_extension = $file_extension;
						if($this->file_extension == '.json') {
							$this->is_json = true;
						}
						$this->file = str_replace($file_extension, '', $file);
						break;
					}
				}
			} else {
				break;
			}
		}
	}

	public function exists() {
		return $this->exists;
	}

	public function setVars($vars) {
		$vars['this'] = $this;
		$this->vars = $vars;
		return $this;
	}

	public function vars($key = null) {
		if (!is_null($key)) {
			$return = null;
			if (isset($this->vars[$key])) {
				$return = $this->vars[$key];
			}
			return $return;
		}
		return $this->vars;
	}

	public function setVar($name, $value) {
		$this->vars[$name] = $value;
		return $this;
	}

	public static function open_buffer() {
		ob_start();
	}

	public static function flush_buffer() {
		ob_end_flush();
	}

	/**
	 * Convert HTML into JSON
	 *
	 * @param string $html The HTML string
	 * @return strong JSON representation of HTML 
	 */
	protected function htmlJson($html) {
		$dom = new DOMDocument();
		$dom->loadHTML($html);

		return json_encode($this->domElementArray($dom->documentElement));
	}

	/**
	 * Convert JSON into HTML
	 *
	 * @param string $json The JSON string
	 * @return strong HTML representation of JSON 
	 */
	protected function jsonHtml($json) {
		$html = '';

		if($nodeArray = json_decode($json, true)) {

			print '<pre>'.print_r($nodeArray, true).'</pre>';

			foreach($nodeArray as $node) {
				$html .= $this->parseNodeHtml($childNode)."\n";
			}
		}

		return $html;
	}

	/**
	 * Parse an array into HTML node
	 *
	 * @param $node array The HTML node as an array
	 * @return string The HTML node string
	 */
	protected function parseNodeHtml($node) {
		$html = '';

		if(isset($node['tag'])) {
			switch($node['tag']) {
				case 'br':
				case 'hr':
					$html .= '<'.$node['tag'];
					foreach($node as $attr_name => $attr_val) {
						if($node_html = $this->parseNodeHtmlAttr($attr_name, $attr_val)) {
							$html .= $node_html.' ';
						}
					}
					$html = trim($html).'>';
					$html .= ' />';
				break;
				default:
					$html .= '<'.$node['tag'];
					foreach($node as $attr_name => $attr_val) {
						if($node_html = $this->parseNodeHtmlAttr($attr_name, $attr_val)) {
							$html .= $node_html.' ';
						}
					}
					$html = trim($html).'>';

					if(isset($node['children'])) {
						$html .= "\n";
						foreach($node['children'] as $childNode) {
							$html .= $this->parseNodeHtml($childNode)."\n";
						}
					} elseif(isset($node['html'])) {
						$html .= $node['html'];
					}

					$html .= '<'.$node['tag'].' />';
				break;
			}
		}

		return $html;
	}

	/**
	 * Parse an HTML node attribute array
	 *
	 * @param string $attr_name The node attribute name
	 * @param string $attr_val The node attribute value
	 * @return string The node attr/value string
	 */
	protected function parseNodeHtmlAttr($attr_name, $attr_val) {
		$html = '';
		switch($node_attr) {
			case 'tag':
			case 'children':
				// skip
			break;
			default:
				if(is_scalar($attr_val)) {
					$html .= $attr_name.'="'.$attr_val.'"';
				}
			break;
		}

		return $hmtl;
	}

	protected function domElementArray($Element) {
		$obj = array( "tag" => $Element->tagName );
		foreach($Element->attributes as $attribute) {
			$obj[$attribute->name] = $attribute->value;
		}
		foreach ($Element->childNodes as $subElement) {
			if($subElement->nodeType == XML_TEXT_NODE) {
				$obj["html"] = $subElement->wholeText;
			} elseif($subElement->nodeType == XML_CDATA_SECTION_NODE) {
				$obj["html"] = $subElement->data;
			} else {
				$obj["children"][] = $this->domElementArray($subElement);
			}
		}

		return $obj;
	}

	/**
	 * Parse JSON into HTML
	 */
	protected function parseJson() {
		$html = '';
		$file = APP_VIEW_PATH.$this->file.$this->file_extension;
		
		if(file_exists($file) && !is_dir($file)) {
			$json_contents = file_get_contents($file);
			$html = $this->jsonHtml(trim($json_contents));

			foreach($this->vars as $_name => $_value) {
				if(false !== strpos($_value, $_name)) {
					$html = str_replace('$'.$_name, $_value);
				}
			}
		}

		return $html;
	}

	public function render() {
		if($this->is_json) {
			die($this->file);
			print $this->parseJson();
		} else {
			if(isset($this->vars)) extract($this->vars);
			if ($this->exists) {
				include_once(APP_VIEW_PATH.$this->file.$this->file_extension);
			}
		}
	}

	public function __toString() {
		return $this->render();
	}
}