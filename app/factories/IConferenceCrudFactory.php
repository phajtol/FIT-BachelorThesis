<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 31.3.2015
 * Time: 16:29
 */

namespace App\Factories;


interface IConferenceCrudFactory {
	/**
	 * @return \App\CrudComponents\Conference\ConferenceCrud
	 */
	function create();
}
?>