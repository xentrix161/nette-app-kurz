<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('prihlasenie/', 'Sign:in');
		$router->addRoute('homepage/', 'Homepage:default');
		$router->addRoute('odhlasenie/', 'Sign:out');
		//TODO: Dorobiť routing na mazanie
//        $router->addRoute('vymazenie/prispevku/<postId>', 'Post:delete');
        $router->addRoute('editacia/prispevku/<postId>', 'Post:manipulate');
        $router->addRoute('zobrazenie/prispevku/<postId>', 'Post:show');
        $router->addRoute('vytvoreniePrispevku/', 'Post:manipulate', Nette\Application\Routers\Route::ONE_WAY);
        $router->addRoute('vytvorenie/prispevku/', 'Post:manipulate');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
