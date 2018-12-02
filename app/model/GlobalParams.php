<?php

namespace App\Model;


class GlobalParams {

    /** @var string public www address */
    protected $public_www_address;

    /** @var string */
    protected $adminEmailAddress;


    /**
     * @return string
     */
    public function getPublicWWWAddress(): string
    {
        return $this->public_www_address;
    }

    /**
     * @return string
     */
    public function getAdminEmailAddress(): string
    {
        return $this->adminEmailAddress;
    }

    // --

    /**
     * GlobalParams constructor.
     * @param $params
     */
    function __construct($params)
    {
        $vars = null;

        if(is_array($params)){
            $vars = $params;
        } elseif(is_object($params)) {
            $vars = get_object_vars($params);
        }

        if($vars && is_array($vars)) {
            foreach($params as $k => $v) {
                if(property_exists($this, $k)) $this->$k = $v;
            }
        }
    }

}