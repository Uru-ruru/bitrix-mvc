<?php

namespace Uru\Tests\SlimApi;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use PHPUnit\Framework\TestCase;
use Uru\SlimApiController\OnlyArgsStrategy;
use Uru\Tests\SlimApi\Stubs\TestController;

class ApiControllerTest extends TestCase
{
    public function testSlimApi()
    {

        $app = AppFactory::create();

        $app->addErrorMiddleware(true, false, false);

        $app->get('/', function (Request $request, Response $response, array $args) {
            return $response->withStatus(200);
        });


        $this->assertSame(App::class, get_class($app));
    }

    public function testBaseApi()
    {
        $app = AppFactory::create();

        $app->addErrorMiddleware(true, false, false);

        $app->get('/testCall', TestController::class . ':testCall');


        $this->assertSame(App::class, get_class($app));
    }

    public function testBaseApiJson()
    {
        $app = AppFactory::create();

        $app->addErrorMiddleware(true, false, false);

        $app->get('/testJson', TestController::class . ':testJson');


        $this->assertSame(App::class, get_class($app));
    }

    public function testBaseApiError()
    {
        $app = AppFactory::create();

        $app->addErrorMiddleware(true, false, false);


        $app->get('/testError', TestController::class . ':testError');


        $this->assertSame(App::class, get_class($app));
    }

    public function testBaseApiOnlyArgs()
    {
        $app = AppFactory::create();

        $routeCollector = $app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new OnlyArgsStrategy());

        $app->addErrorMiddleware(true, false, false);


        $app->get('/testOnlyArgs', TestController::class . ':testOnlyArgs');


        $this->assertSame(App::class, get_class($app));
    }

}
