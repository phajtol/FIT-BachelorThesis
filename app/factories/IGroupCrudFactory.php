<?php

namespace App\Factories;

use App\CrudComponents\Group\GroupCrud;


interface IGroupCrudFactory {

	/** @return \App\CrudComponents\Group\GroupCrud */
	public function create(): GroupCrud;

}
