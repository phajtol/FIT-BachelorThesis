<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.5.2015
 * Time: 18:31
 */

namespace App\Factories;

interface IPublicationCategoryListFactory {

	/**
	 * @return \App\Components\PublicationCategoryList\PublicationCategoryListComponent
	 */
	public function create();

}