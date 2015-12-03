<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 5.4.2015
 * Time: 23:02
 */

namespace App\Interfaces;


interface IAuthMethodTranslator {

	/**
	 * Translates auth method id to human-readable auth method text
	 * @param $authMethod string
	 * @return string
	 */
	public function translateAuthMethod($authMethod);

}