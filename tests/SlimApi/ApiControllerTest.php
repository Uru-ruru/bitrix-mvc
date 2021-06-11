<?php

namespace Uru\Tests\SlimApi;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Uru\SlimApiController\OnlyArgsStrategy;
use Uru\Tests\SlimApi\Stubs\TestController;

class ApiControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSlimApi()
    {
        $app = $this->getAppInstance();

        $app->get('/test-action-response-code', function (Request $request, Response $response, array $args) {
            return $response->withStatus(200);
        });

        $request = $this->createRequest('GET', '/test-action-response-code');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testBaseApi()
    {
        $app = $this->getAppInstance();

        $app->get('/testCall', TestController::class . ':testCall');

        $request = $this->createRequest('GET', '/testCall');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testBaseApiJson()
    {
        $app = $this->getAppInstance();

        $app->get('/testJson', TestController::class . ':testJson');

        $request = $this->createRequest('GET', '/testJson');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();

        $this->assertEquals(json_encode(TestController::JSON), $payload);
    }

    /**
     * @throws Exception
     */
    public function testBaseApiError()
    {
        $app = $this->getAppInstance();

        $app->get('/testError', TestController::class . ':testError');

        $request = $this->createRequest('GET', '/testError');
        $response = $app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testBaseApiOnlyArgs()
    {
        $app = $this->getAppInstance();

        $routeCollector = $app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new OnlyArgsStrategy());

        $app->addErrorMiddleware(true, false, false);

        $app->get('/testOnlyArgs', TestController::class . ':testOnlyArgs');

        $request = $this->createRequest('GET', '/testOnlyArgs');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

}
