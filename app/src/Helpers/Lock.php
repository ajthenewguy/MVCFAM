<?php namespace MVCFAM\App\Helpers;
/**
 * Lock class is used in security.
 *
 * Lock files should be stored on a secure sever, separate from the server this
 * application runs on, and transmitted via the API.
 *
 * [Database A  ]
 * [Table 1     ]
 * [ID|Username|Password|Date]
 * [ 1| amccabe| ef75gh4|2016]
 * ---------------------------
 * .keys/ef75gh4  -- Contains the encrypted data
 * 
 */

/*

concatentate Encryptor objects...?

*/

use MVCFAM\App\AnObj;

 class Lock {

 	private $input;

 	/**
 	 * The filename of the encrypted data.
 	 */
 	private $key;

 	/**
 	 *
 	 */
 	private $encrypted;

 	private $debug = true;


 	public function __construct($key) {
 		$this->set_key($key);
 	}

 	private function set_key($key) {
 		$this->key = $key;
 		return $this;
 	}

 	public function unlock($pass) {
 		if (! $this->encrypted) {

 		}
 	}

 	public function encrypt() {

 	}

 	public function decrypt() {

 	}

 	public function get_cipher($id) {
 		$Cipher = new AnObj;
 		switch ($id) {
 			case 0: // ^ Xor (exclusive or) - Bits that are set in $a or $b but not both are set.
 				$Cipher->encrypt = function($input) {
 					$output = $input ^ 1;
 					return $output;
 				};
 				$Cipher->decrypt = function($input) {
 					$output = $input ^ 1;
 					return $output;
 				};
 			break;
 			case 1: // << Shift left - Shift the bits of $a $b steps to the left (each step means "multiply by two")
 				$Cipher->encrypt = function($input) {
 					$output = $input << 1;
 					return $output;
 				};
 				$Cipher->decrypt = function($input) {
 					$output = $input >> 1;
 					return $output;
 				};
 			break;
 			case 2: // >> Shift right - Shift the bits of $a $b steps to the right (each step means "divide by two")
 				$Cipher->encrypt = function($input) {
 					$output = $input >> 1;
 					return $output;
 				};
 				$Cipher->decrypt = function($input) {
 					$output = $input << 1;
 					return $output;
 				};
 			break;
 			case 3: // & And - Bits that are set in both $a and $b are set.
 				$Cipher->encrypt = function($input) {
 					$output = $input;
 					return $output;
 				};
 				$Cipher->decrypt = function($input) {
 					$output = $input;
 					return $output;
 				};
 			break;
 			case 4: // | Or (inclusive or) - Bits that are set in either $a or $b are set.
 				$Cipher->encrypt = function($input) {
 					$output = $input;
 					return $output;
 				};
 				$Cipher->decrypt = function($input) {
 					$output = $input;
 					return $output;
 				};
 			break;
 			case 5: // ~ Not - Bits that are set in $a are not set, and vice versa.
 				$Cipher->encrypt = function($input) {
 					$output = $input;
 					return $output;
 				};
 				$Cipher->decrypt = function($input) {
 					$output = $input;
 					return $output;
 				};
 			break;
 		}
 		return $Cipher;
 	}

 	/**
 	 * Used in decrypting:
 	 * 		Lock::time_int(path/to/file
 	 */
 	public static function time_int($file = null) {
 		return (is_null($file) ?  filemtime($file) : strtotime());
 	}

 	/**
 	 * Creates and returns a new unique Lock
 	 */
 	public static function create($password = 1234, $data = null) {
 		// DEVELOPMENT
 		//  Returns a closure that accepts an input and returns the computed value
 		/*
		$Lock = Lock::create();

		$Lock('pass0rd att3mpt'); // what should this do?


		---------------------

		Eventually this method should write a new php class file that extends
		this one and contains a unique mechanism
 		*/

		// Validate password

		// Encode objects/arrays
 		if (! is_scalar($data)) {
 			$data = json_encode($data);
 		}
		$key = md5($data);
		$salt = md5($key);
 		$Lock = new static($key);



 		return function($guess, $ordAdapter = false) use ($Lock) {
 			if (is_array($guess)) {
 				$guess = self::ordAdapter($guess);
 				$ordAdapter = true;
 			}
 			$result = $Lock->mechanism($guess, $ordAdapter);
		    return array('input' => $guess, 'hash' => $result);
		};
 	}

 	public static function ordAdapter($input = []) {
 		$string = '';
 		foreach ($input as $ord) {
 			$string .= chr($ord);
 		}
 		return $string;
 	}

 	private function mechanism($input) {
 		$in = str_split($input);
 		$o = [];
 		while($chr = array_shift($in)) {
 			$int = ord($chr);
 			if ($int < 64) {
 				if ($int < 32) {
 					if (0 == $int % 2) {
 						$o[] = $int >> (4 % $int ?: 1);
					} else {
						$o[] = $int << (4 % $int ?: 1);
					}
 				} else { // $int >= 32 && < 64
 					if ($int > 49) {
 						if (0 == $int % 0x02) {
	 						$o[] = $int >> (5 % $int) + 1;
						} else {
							$o[] = $int << (5 % $int) + 1;
						}
 					} else { // $int > 32 && <= 49
 						$int = (1 == $int % 0x02 ? ($int << ($int % 3) + 1) : ($int >> ($int % 3) + 1));
 						if ($int === 0) {
 							$o[] = '0';
 						} else {
	 						if (0 == $int % 0x02) {
	 							$int = intval((0 === $int % 4 ? sqrt($int) : pow($int, 2)));
	 						} else {
	 							$int -= 1;
	 						}
	 						if (0 == $int % 0x02) {
 								$int /= 2;
 							} else {
 								$int *= 2;
 							}
	 						if (0 === ($int % 3)) {
	 							$o[] = $int / 3;
	 						} else {
	 							$o[] = ($int | ($int % 5) + 1);
	 						}
 						}
 					}
 				}
 			} else { // $int >= 64
 				if ($int >= 0x040) {
 					if ($int > 128) {
 						throw new \Exception('%d out of bounds', $int);
 					} else {
 						if (0 == $int % 0x02) {
	 						$int = $int >> 1;
	 					} else {
	 						$int = $int << 1;
	 					}
	 					$o[] = $int + 1;
 					}
 				} else { // int == 64
 					$o[] = ord(@([].''));
 				}
 			}
 		}
 		return implode('-', $o);
 	}

 	/**
 	 * Send a string to output
 	 */
 	protected function debug($str = '') {
 		if ($this->debug) {
	 		if (! is_scalar($str))
	 			print_r($str)."\n";
	 		else
 				print $str."\n";
 		}
 	}

 	/**
 	 * API
 	 */


 }