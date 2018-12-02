<?php

namespace App\Factories;

use App\CrudComponents\ConferenceYear\ConferenceYearCrud;


interface IConferenceYearCrudFactory {

    /**
     * @param int $conferenceId
     * @return \App\CrudComponents\ConferenceYear\ConferenceYearCrud
     */
	public function create(int $conferenceId): ConferenceYearCrud;

}