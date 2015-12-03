<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 7.4.2015
 * Time: 20:04
 */

namespace App\Factories;


interface IFavouriteConferenceToggleFactory {

	/**
	 * @param $userId
	 * @param $conferenceId
	 * @return \App\Components\FavouriteConferenceToggle\FavouriteConferenceToggleComponent
	 */
	public function create($userId, $conferenceId);

}