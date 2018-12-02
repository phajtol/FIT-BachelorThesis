<?php

namespace App\Factories;

use App\CrudComponents\Reference\ReferenceCrud;


interface IReferenceCrudFactory {

    /**
     * @param int $publicationId
     * @return \App\CrudComponents\Reference\ReferenceCrud
     */
	public function create(int $publicationId): ReferenceCrud;

}
