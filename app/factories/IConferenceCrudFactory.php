<?php

namespace App\Factories;

use App\CrudComponents\Conference\ConferenceCrud;


interface IConferenceCrudFactory {
	/**
	 * @return \App\CrudComponents\Conference\ConferenceCrud
	 */
	function create(): ConferenceCrud;
}
