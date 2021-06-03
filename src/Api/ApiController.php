<?php

namespace Uru\SlimApiController;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

abstract class ApiController
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ApiController constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get('request');
        $this->response = $container->get('response');
        $this->logger = $container->get('logger');
    }

    /**
     * Respond with error message and code.
     *
     * @param string $message
     * @param int $code
     * @return ResponseInterface
     */
    protected function respondWithError(string $message = 'No specific error message was specified', int $code = 400): ResponseInterface
    {
        $json = [
            'error' => [
                'http_code' => $code,
                'message'   => $message,
            ]
        ];

        return $this->response->withStatus($code)->withJson($json);
    }

    /**
     * 403 error.
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function errorForbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->respondWithError($message, 403);
    }

    /**
     * 500 error.
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function errorInternalError(string $message = 'Internal Error'): ResponseInterface
    {
        return $this->respondWithError($message, 500);
    }

    /**
     * 404 error
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function errorNotFound(string $message = 'Resource Not Found'): ResponseInterface
    {
        return $this->respondWithError($message, 404);
    }

    /**
     * 401 error.
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function errorUnauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->respondWithError($message, 401);
    }

    /**
     * 400 error.
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function errorWrongArgs(string $message = 'Wrong Arguments'): ResponseInterface
    {
        return $this->respondWithError($message);
    }

    /**
     * 501 error.
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function errorNotImplemented(string $message = 'Not implemented'): ResponseInterface
    {
        return $this->respondWithError($message, 501);
    }
}
