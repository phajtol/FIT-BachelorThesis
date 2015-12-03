<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.4.2015
 * Time: 22:23
 */

namespace App\Factories;


interface IConferenceCategoryListFactory {

	/**
	 * @return \App\Components\ConferenceCategoryList\ConferenceCategoryListComponent
	 */
	public function create();

}