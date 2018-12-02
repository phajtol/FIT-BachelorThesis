<?php

namespace App\Model;

use Nette;

class Database {

    use Nette\SmartObject;


    /** @var Nette\Database\Context */
    public $database;

    /**
     * Database constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

}
