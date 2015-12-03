<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 3.5.2015
 * Time: 17:07
 */

namespace App\Factories;


interface IPublisherCrudFactory {

	/** @return \App\CrudComponents\Publisher\PublisherCrud */
	public function create();

}