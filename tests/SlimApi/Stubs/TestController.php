<?php


namespace Uru\Tests\SlimApi\Stubs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Uru\SlimApiController\ApiController;

class TestController extends ApiController
{
    const JSON = ['test' => true];

    public function testCall(Request $request, Response $response): Response
    {
        return $response->withStatus(200);
    }

    public function testJson(Request $request, Response $response): Response
    {
        return $this->withJson($request, $response, self::JSON)->withStatus(200);
    }

    public function testError(Request $request, Response $response): Response
    {
        return $this->respondWithError($request, $response);
    }

    public function testOnlyArgs(): Response
    {
        $response = new \Slim\Psr7\Response();
        return $response->withStatus(200);
    }
}
