<?php

namespace App\Factories;


interface IReferenceCrudFactory {

	/** @return \App\CrudComponents\Reference\ReferenceCrud */
	public function create($publicationId);

}
