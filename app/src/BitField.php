<?php namespace MVCFAM\App;
/**
 * BitField class. Extensions must define values:
 *   class UserPrivacySettings_BitField extends BitField
 *   {
 *       const PRIVACY_EMAIL = 1;
 *       const PRIVACY_NAME = 2;
 *       const PRIVACY_ADDRESS = 4;
 *       const PRIVACY_PHONE = 8;
 *       const PRIVACY_ALL = 15;
 *   }
 *   
 *   $bf = new UserPrivacySettings_BitField();
 *   echo "Setting PRIVACY_EMAIL<br/>";
 *   $bf->set(UserPrivacySettings_BitField::PRIVACY_EMAIL);
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_EMAIL));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_NAME));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_ADDRESS));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_PHONE));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_ALL));
 *   echo "Setting PRIVACY_NAME<br/>";
 *   $bf->set(UserPrivacySettings_BitField::PRIVACY_NAME);
 *   echo "Setting PRIVACY_ADDRESS<br/>";
 *   $bf->set(UserPrivacySettings_BitField::PRIVACY_ADDRESS);
 *   echo "Setting PRIVACY_PHONE<br/>";
 *   $bf->set(UserPrivacySettings_BitField::PRIVACY_PHONE);
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_EMAIL));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_NAME));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_ADDRESS));
 *   var_dump($bf->get(UserPrivacySettings_BitField::PRIVACY_PHONE));
 * 
 */

use MVCFAM\App\Object;

abstract class BitField extends Object {

    private $value;

    public function __construct($value=0) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function get($n) {
        return ($this->value & $n) == $n;
    }

    public function set($n) {
        $this->value |= $n;
    }

    public function clear($n) {
        $this->value &= ~$n;
    }
}
