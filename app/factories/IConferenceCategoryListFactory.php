<?php

namespace App\Factories;

use App\Components\ConferenceCategoryList\ConferenceCategoryListComponent;


interface IConferenceCategoryListFactory {

	/**
	 * @return \App\Components\ConferenceCategoryList\ConferenceCategoryListComponent
	 */
	public function create(): ConferenceCategoryListComponent;

}