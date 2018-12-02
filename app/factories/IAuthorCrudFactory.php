<?php

namespace App\Factories;

use App\CrudComponents\Author\AuthorCrud;


interface IAuthorCrudFactory {

	/** @return \App\CrudComponents\Author\AuthorCrud */
	public function create(): AuthorCrud;

}