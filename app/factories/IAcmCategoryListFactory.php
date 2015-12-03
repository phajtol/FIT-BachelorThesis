<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 19:59
 */

namespace App\Factories;


interface IAcmCategoryListFactory {

	/**
	 * @return \App\Components\AcmCategoryList\AcmCategoryListComponent
	 */
	public function create();

}