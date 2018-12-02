<?php

namespace App\Factories;

use App\CrudComponents\PublicationTag\PublicationTagCrud;

interface IPublicationTagCrudFactory {

    /**
     * @param int $publicationId
     * @return \App\CrudComponents\PublicationTag\PublicationTagCrud
     */
	public function create(int $publicationId): PublicationTagCrud;

}
