<?php

namespace App\Factories;

use App\Components\FavouriteConferenceToggle\FavouriteConferenceToggleComponent;

interface IFavouriteConferenceToggleFactory {

	/**
	 * @param int $userId
	 * @param int $conferenceId
	 * @return \App\Components\FavouriteConferenceToggle\FavouriteConferenceToggleComponent
	 */
	public function create(int $userId, int $conferenceId): FavouriteConferenceToggleComponent;

}