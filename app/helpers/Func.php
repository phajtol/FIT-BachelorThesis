<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 5.4.2015
 * Time: 17:38
 */

namespace App\Helpers;


class Func {

	public static function getAndUnset(&$array, $key) {
		if(isset($array[$key])) {
			$v = $array[$key];
			unset($array[$key]);
			return $v;
		} else return null;
	}

	/**
	 * If value(s) of given key(s) is/are determined as false or not found, set it/them to NULL
	 * @param $array
	 * @param $key string|string[]
	 */
	public static function valOrNull(&$array, $key) {
		if(!is_array($key)) $key = array($key);
		foreach($key as $k) {
			if (!isset($array[$k]) || !$array[$k]) $array[$k] = null;
		}
	}

	public static function getValOrNull($array, $key) {
		if(isset($array[$key])) return $array[$key]; else return null;
	}

}