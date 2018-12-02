<?php

namespace App\Factories;

use App\Components\PublicationCategoryList\PublicationCategoryListComponent;

interface IPublicationCategoryListFactory {

	/**
	 * @return \App\Components\PublicationCategoryList\PublicationCategoryListComponent
	 */
	public function create(): PublicationCategoryListComponent;

}