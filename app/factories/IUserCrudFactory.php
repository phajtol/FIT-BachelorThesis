<?php

namespace App\Factories;

use App\CrudComponents\User\UserCrudComponent;


interface IUserCrudFactory {

	/**
	 * @return \App\CrudComponents\User\UserCrudComponent
	 */
	public function create(): UserCrudComponent;

}
