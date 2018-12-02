<?php

namespace App\Factories;

use App\CrudComponents\Publisher\PublisherCrud;


interface IPublisherCrudFactory {

	/** @return \App\CrudComponents\Publisher\PublisherCrud */
	public function create(): PublisherCrud;

}