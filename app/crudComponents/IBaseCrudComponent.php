<?php

namespace App\CrudComponents;


interface IBaseCrudComponent {

	public function handleDelete(int $id): void;

	public function handleEdit(int $id): void;
}