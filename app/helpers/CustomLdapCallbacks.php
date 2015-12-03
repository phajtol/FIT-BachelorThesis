<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 9.3.2015
 * Time: 21:24
 */

namespace App\Helpers;


class CustomLdapCallbacks {

	/**
	 * @var \App\Model\AuthLdap
	 */
	protected $authLdapModel;

	/**
	 * @var string basedn template, ex. 'uid=%s,ou=People,o=fit.cvut.cz'
	 */
	protected $dnTemplate;


	function __construct(\App\Model\AuthLdap $authLdapModel, $dnTemplate)
	{
		$this->authLdapModel = $authLdapModel;
		$this->dnTemplate = $dnTemplate;
	}


	public function getUserInfoCallback(){
		return function(\Toyota\Component\Ldap\Core\Manager $ldap, array $userData){

			$raw = $ldap->search(sprintf($this->dnTemplate, $userData["username"]), null, true, null);


			if(!$raw->current() instanceof \Toyota\Component\Ldap\Core\Node) {
				return array();
			}
			$attrs = $raw->current()->getAttributes();


			// Post process & return
			$return = array();
			foreach($attrs as $key => $val) {
				/** @var \Toyota\Component\Ldap\Core\NodeAttribute $val */
				$rkey = $key;// $this->loadInfo[$key];
				$return[$rkey] = $val->getValues();
				if(count($return[$rkey]) === 1) {
					$return[$rkey] = reset($return[$rkey]);
				}
			}

			return array(
				"name"		=>	$return["givenName"],
				"surname"	=>	$return["sn"],
				"email"		=>	$return["mail"]
			);
		};
	}
}

?>