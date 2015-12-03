<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 23:38
 */

namespace App\Factories;


interface IAuthorCrudFactory {

	/** @return \App\CrudComponents\Author\AuthorCrud */
	public function create();

}