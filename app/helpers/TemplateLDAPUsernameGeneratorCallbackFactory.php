<?php
// UNUSED!

/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 4.3.2015
 * Time: 17:25
 */

namespace app\model;


class TemplateLDAPUsernameGeneratorCallbackFactory {

	public static function createGenerator($template) {
		return function($ldap, $username) use ($template) {
			return sprintf($template, $username);
		};
	}

}
?>