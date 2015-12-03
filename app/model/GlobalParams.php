<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.3.2015
 * Time: 11:49
 */

namespace App\Model;


class GlobalParams {

    /**
     * @var string public www address
     */
    protected $public_www_address;

    /**
     * @var string
     */
    protected $adminEmailAddress;


    /**
     * @return string
     */
    public function getPublicWWWAddress()
    {
        return $this->public_www_address;
    }

    /**
     * @return string
     */
    public function getAdminEmailAddress() {
        return $this->adminEmailAddress;
    }

    // --

    function __construct($params) {
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