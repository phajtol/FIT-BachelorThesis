<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 4.4.2015
 * Time: 0:11
 */

namespace App\Factories;


interface IUserCrudFactory {

	/**
	 * @return \App\CrudComponents\User\UserCrudComponent
	 */
	public function create();

}
