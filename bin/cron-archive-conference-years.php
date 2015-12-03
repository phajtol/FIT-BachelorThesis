<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 4.5.2015
 * Time: 13:41
 */

$container = require __DIR__ . '/../app/bootstrap.php';
/**
 * @var $container \Nette\DI\Container
 */

$db = $container->getByType('\Nette\Database\Connection');
/**
 * @var $db Nette\Database\Connection
 */

$r = $db->query("UPDATE `conference_year` SET `state` = 'archived' WHERE
	state = 'alive' AND (
		(w_year IS NOT NULL AND w_year < " . date("Y") . ") OR
		(w_to IS NOT NULL AND year(w_to) < year(now()))
	)
 ");
