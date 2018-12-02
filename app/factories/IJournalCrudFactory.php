<?php

namespace App\Factories;

use App\CrudComponents\Journal\JournalCrud;


interface IJournalCrudFactory {

	/** @return \App\CrudComponents\Journal\JournalCrud */
	public function create(): JournalCrud;
}