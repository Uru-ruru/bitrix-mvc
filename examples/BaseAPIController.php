<?php

use Uru\SlimApiController\ApiController;
use Slim\Http\Response;

/**
 * Class BaseController
 *
 * @property Response $response
 */
abstract class BaseAPIController extends ApiController
{
    /**
     * Получить значение из запроса
     * @param $key
     * @return mixed
     */
    protected function getParam($key)
    {
        $request = array_merge($this->request->getQueryParams(), (array)$this->request->getParsedBody());
        return $request[$key];
    }
}
