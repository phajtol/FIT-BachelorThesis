<?php

namespace App\Factories;


interface ICitationCrudFactory {

	/** @return \App\CrudComponents\Citation\CitationCrud */
	public function create($publicationId);

}
