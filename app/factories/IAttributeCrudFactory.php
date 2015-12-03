<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 21:28
 */

namespace App\Factories;


interface IAttributeCrudFactory {

	/** @return \App\CrudComponents\Attribute\AttributeCrud */
	public function create();

}