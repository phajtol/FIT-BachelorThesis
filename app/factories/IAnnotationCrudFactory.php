<?php

namespace App\Factories;

use App\CrudComponents\Annotation\AnnotationCrud;


interface IAnnotationCrudFactory {

    /**
     * @param int $publicationId
     * @return \App\CrudComponents\Annotation\AnnotationCrud
     */
	public function create(int $publicationId): AnnotationCrud;

}