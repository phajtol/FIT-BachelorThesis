<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 23:00
 */

namespace App\Factories;


interface IGroupCrudFactory {

	/** @return \App\CrudComponents\Group\GroupCrud */
	public function create();

}
