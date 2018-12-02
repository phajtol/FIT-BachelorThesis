<?php

namespace App\Model;


use Nette\SmartObject;

class ShibbolethParams {

    use SmartObject;

	protected $params;

    /**
     * ShibbolethParams constructor.
     * @param $assocParams
     */
	public function __construct($assocParams)
    {
		$this->params = $assocParams;
	}

    /**
     * @return null
     */
	public function getGroupRoles()
    {
		if (isset($this->params['groupRoles'])) {
		    return $this->params['groupRoles'];
        } else {
		    return null;
        }
	}

    /**
     * @return null
     */
	public function getDefaultRoles()
    {
		if (isset($this->params['defaultRoles'])) {
		    return $this->params['defaultRoles'];
        } else {
		    return null;
        }
	}


}