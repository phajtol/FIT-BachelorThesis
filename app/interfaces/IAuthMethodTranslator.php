<?php

namespace App\Interfaces;


interface IAuthMethodTranslator {

	/**
	 * Translates auth method id to human-readable auth method text
	 * @param string|null $authMethod
	 * @return string|null
	 */
	public function translateAuthMethod(?string $authMethod): ?string;

}