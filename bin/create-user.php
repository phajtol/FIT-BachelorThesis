<?php

if (!isset($_SERVER['argv'][3])) {
	echo '
Add new user to database.

Usage: create-user.php <name> <password> <email>
';
	exit(1);
}

list(, $user, $password, $email) = $_SERVER['argv'];

$container = require __DIR__ . '/../app/bootstrap.php';
/**
 * @var $container \Nette\DI\Container
 */

$baseAuthenticator = $container->getService("BaseAuthenticator");
/**
 * @var $baseAuthenticator \App\Services\Authenticators\BaseAuthenticator
 */

$lpAuthenticator = new \App\Services\Authenticators\LoginPassAuthenticator($container->getByType('\App\Model\AuthLoginPassword'));

$new_user_id = $baseAuthenticator->createNewUser($user, $baseAuthenticator::DEFAULT_ROLE, $email);

if($new_user_id) {
	$lpAuthenticator->associateLoginPasswordToUser($new_user_id, $user, $password);
} else echo "ERROR\n";


//$container->getByType('App\UserManager')->add($user, $password);

echo "User $user was added.\n";
