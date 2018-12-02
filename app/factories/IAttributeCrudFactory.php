<?php

namespace App\Factories;

use App\CrudComponents\Attribute\AttributeCrud;


interface IAttributeCrudFactory {

	/** @return \App\CrudComponents\Attribute\AttributeCrud */
	public function create(): AttributeCrud;

}