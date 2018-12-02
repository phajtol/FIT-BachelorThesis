<?php

namespace App\Factories;

use App\CrudComponents\CuGroup\CuGroupCrud;


interface ICuGroupCrudFactory {

	/**
	 * @return \App\CrudComponents\CuGroup\CuGroupCrud
	 */
	public function create(): CuGroupCrud;

}