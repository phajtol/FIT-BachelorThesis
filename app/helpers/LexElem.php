<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Helpers;

use Nette;

class LexElem {

    use Nette\SmartObject;

    public $symbol;
    public $sattribute;

    public function __construct($symbol, $sattribute) {
        $this->symbol = $symbol;
        $this->sattribute = $sattribute;
    }

}
