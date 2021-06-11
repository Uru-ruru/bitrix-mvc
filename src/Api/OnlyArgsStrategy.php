<?php

namespace Uru\SlimApiController;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class OnlyArgsStrategy implements InvocationStrategyInterface
{

    /**
     * Invoke a route callable without request and response,
     *
     * @param array|callable $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $routeArguments
     *
     * @return ResponseInterface
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface
    {
        return $callable($routeArguments);
    }
}
