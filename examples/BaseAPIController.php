<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Uru\SlimApiController\ApiController;

class BaseAPIController extends ApiController
{
    public function testCall(Request $request, Response $response): Response
    {
        return $response->withStatus(200);
    }

    public function testJson(Request $request, Response $response): Response
    {
        return $this->withJson($request, $response, ['test' => 'test'])->withStatus(200);
    }
}
