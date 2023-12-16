<?php

namespace Uru\SlimApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class ApiController.
 */
abstract class ApiController
{
    public function withJson(Request $request, Response $response, $data): Response
    {
        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
        ;
    }

    public function withRedirect(Request $request, Response $response, string $url, int $status = 302): Response
    {
        return $response
            ->withHeader('Location', $url)
            ->withStatus($status)
        ;
    }

    /**
     * Respond with error message and code.
     */
    protected function respondWithError(Request $request, Response $response, string $message = 'No specific error message was specified', int $code = 400): Response
    {
        $json = [
            'error' => [
                'http_code' => $code,
                'message' => $message,
            ],
        ];

        return $this->withJson($request, $response, $json)->withStatus($code);
    }

    /**
     * 403 error.
     */
    protected function errorForbidden(Request $request, Response $response, string $message = 'Forbidden'): Response
    {
        return $this->respondWithError($request, $response, $message, 403);
    }

    /**
     * 500 error.
     */
    protected function errorInternalError(Request $request, Response $response, string $message = 'Internal Error'): Response
    {
        return $this->respondWithError($request, $response, $message, 500);
    }

    /**
     * 404 error.
     */
    protected function errorNotFound(Request $request, Response $response, string $message = 'Resource Not Found'): Response
    {
        return $this->respondWithError($request, $response, $message, 404);
    }

    /**
     * 401 error.
     */
    protected function errorUnauthorized(Request $request, Response $response, string $message = 'Unauthorized'): Response
    {
        return $this->respondWithError($request, $response, $message, 401);
    }

    /**
     * 400 error.
     */
    protected function errorWrongArgs(Request $request, Response $response, string $message = 'Wrong Arguments'): Response
    {
        return $this->respondWithError($request, $response, $message);
    }

    /**
     * 501 error.
     */
    protected function errorNotImplemented(Request $request, Response $response, string $message = 'Not implemented'): Response
    {
        return $this->respondWithError($request, $response, $message, 501);
    }

    /**
     * Получить значение из запроса.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    protected function getParam(Request $request, Response $response, $key)
    {
        $request = array_merge((array) $request->getQueryParams(), (array) $request->getParsedBody());

        return count($request) > 0 && $request[$key] ? $request[$key] : null;
    }
}
