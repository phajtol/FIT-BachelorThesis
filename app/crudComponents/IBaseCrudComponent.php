<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 17.3.2015
 * Time: 17:32
 */

namespace App\CrudComponents;


interface IBaseCrudComponent {
	public function handleDelete($id);
	public function handleEdit($id);
}