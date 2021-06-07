<?php


namespace Uru\Tests\SlimApi\Stubs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Uru\SlimApiController\ApiController;

class TestController extends ApiController
{

    public function testCall(Request $request, Response $response): Response
    {
        return $response->withStatus(200);
    }

    public function testJson(Request $request, Response $response): Response
    {
        $param = $this->getParam($request, $response, 'test');
        return $this->withJson($request, $response, ['test' => $param])->withStatus(200);
    }

    public function testError(Request $request, Response $response): Response
    {
        return $this->respondWithError($request, $response);
    }
}
