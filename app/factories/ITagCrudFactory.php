<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 12:16
 */

namespace App\Factories;


interface ITagCrudFactory {

	/** @return \App\CrudComponents\Tag\TagCrud */
	public function create();

}
