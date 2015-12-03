<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 4.3.2015
 * Time: 17:44
 */

namespace App\Helpers;


use Toyota\Component\Ldap\API\DriverInterface;
use Toyota\Component\Ldap\Core\Manager;
use Toyota\Component\Ldap\Platform\Native\Driver;

class CustomLdapManager extends Manager {

	/**
	 * @var string template to be used for username generation (sprintf($template, $username) will be called to achieve that)
	 */
	protected $loginTemplate;

	public function __construct(array $params, $loginTemplate)
	{
		$this->loginTemplate = $loginTemplate;
		isset($params['baseDn']) ? $params['base_dn'] = $params['baseDn'] : null;
		isset($params['bindDn']) ? $params['bind_dn'] = $params['bindDn'] : null;
		parent::__construct($params, new Driver());
	}

	public function bind($name = null, $password = null)
	{
		if($name !== null) {
			$name = sprintf($this->loginTemplate, $name);
		}
		return parent::bind($name, $password);
	}


}

?>