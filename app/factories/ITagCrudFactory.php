<?php

namespace App\Factories;

use App\CrudComponents\Tag\TagCrud;


interface ITagCrudFactory {

	/** @return \App\CrudComponents\Tag\TagCrud */
	public function create(): TagCrud;

}
