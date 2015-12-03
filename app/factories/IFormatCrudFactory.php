<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 4.5.2015
 * Time: 10:24
 */

namespace App\Factories;


interface IFormatCrudFactory {

	/** @return \App\CrudComponents\Format\FormatCrud */
	public function create();

}