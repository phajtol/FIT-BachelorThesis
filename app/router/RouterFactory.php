<?php

namespace App;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\IRouter;


/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @return IRouter
     */
	public function createRouter(): IRouter
	{
		$router = new RouteList();
		//$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default', RouteList::SECURED);
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
