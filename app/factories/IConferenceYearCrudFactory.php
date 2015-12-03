<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 7.4.2015
 * Time: 18:46
 */

namespace App\Factories;


interface IConferenceYearCrudFactory {

	/**
	 * @return \App\CrudComponents\ConferenceYear\ConferenceYearCrud
	 */
	public function create($conferenceId);

}