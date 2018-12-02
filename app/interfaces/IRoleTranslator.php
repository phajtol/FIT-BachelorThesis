<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 12.3.2015
 * Time: 17:02
 */

namespace App\Interfaces;


interface IRoleTranslator {

	/**
	 * Translates role id to human-readable role text
	 * @param $roleId string
	 * @return string
	 */
	public function translateRole(string $roleId): string;

}