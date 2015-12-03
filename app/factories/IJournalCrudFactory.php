<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 17:08
 */

namespace App\Factories;


interface IJournalCrudFactory {

	/** @return \App\CrudComponents\Journal\JournalCrud */
	public function create();
}