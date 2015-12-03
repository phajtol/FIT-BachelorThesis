<?php

namespace App\Model;

use Nette;

class Database extends Nette\Object {

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

}
