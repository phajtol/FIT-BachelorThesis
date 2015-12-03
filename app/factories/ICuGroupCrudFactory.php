<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.4.2015
 * Time: 3:05
 */

namespace App\Factories;


interface ICuGroupCrudFactory {

	/**
	 * @return \App\CrudComponents\CuGroup\CuGroupCrud
	 */
	public function create();

}