<?php

namespace App\Factories;

use App\CrudComponents\Citation\CitationCrud;

interface ICitationCrudFactory {

    /**
     * @param int $publicationId
     * @return \App\CrudComponents\Citation\CitationCrud
     */
	public function create(int $publicationId): CitationCrud;

}
