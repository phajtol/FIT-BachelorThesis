<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.4.2015
 * Time: 17:04
 */

namespace App\Factories;


interface IDocumentIndexCrudFactory {

	/**
	 * @return \App\CrudComponents\DocumentIndex\DocumentIndexCrud
	 */
	public function create();

}