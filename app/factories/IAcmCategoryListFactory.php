<?php

namespace App\Factories;

use App\Components\AcmCategoryList\AcmCategoryListComponent;


interface IAcmCategoryListFactory {

	/**
	 * @return \App\Components\AcmCategoryList\AcmCategoryListComponent
	 */
	public function create(): AcmCategoryListComponent;

}