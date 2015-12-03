<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 12:16
 */

namespace App\Factories;


interface IAnnotationCrudFactory {

	/** @return \App\CrudComponents\Annotation\AnnotationCrud */
	public function create($publicationId);

}