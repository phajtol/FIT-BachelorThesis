<?php

namespace App\Factories;

use App\CrudComponents\DocumentIndex\DocumentIndexCrud;


interface IDocumentIndexCrudFactory {

	/**
	 * @return \App\CrudComponents\DocumentIndex\DocumentIndexCrud
	 */
	public function create(): DocumentIndexCrud;

}