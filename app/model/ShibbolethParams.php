<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 6.5.2015
 * Time: 17:06
 */

namespace App\Model;


use Nette\SmartObject;

class ShibbolethParams {

    use SmartObject;

	protected $params;

	public function __construct($assocParams) {
		$this->params = $assocParams;
	}

	public function getGroupRoles(){
		if(isset($this->params['groupRoles'])) return $this->params['groupRoles']; else return null;
	}

	public function getDefaultRoles(){
		if(isset($this->params['defaultRoles'])) return $this->params['defaultRoles']; else return null;
	}


}
?>