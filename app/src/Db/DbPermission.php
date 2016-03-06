<?php namespace MVCFAM\App\Db;
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
 */

use MVCFAM\App\Object;
use MVCFAM\App\BitField;

class DbPermission extends BitField {

	const SELECT = 1;
	const INDEX = 2;
	const CREATE = 4;
	const INSERT = 8;
	const UPDATE = 16;
	const DELETE = 32;
	const ALTER = 64;
	const DROP = 128;
	const SHOW_DATABASES = 256;
	const ALL = 512;
}