<?php

namespace Iwgb\Join\Handler\Api\Onboarding;

use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use Iwgb\Join\Handler\RootHandler;
use Iwgb\Join\Provider\Provider;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Teapot\StatusCode;

class JobTypeProxy extends RootHandler {

    private const JOB_TYPE_WORKSPACE = 'mhvN5b';
    private const JOB_TYPE_PAGE_SIZE = 20;
    private const TYPEFORM_BASE_URL = 'https://api.typeform.com/forms';

    protected GuzzleHttp\Client $http;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->http = $c[Provider::HTTP];
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $data = $this->processTypeformProxy($request->getMethod(), $args);

        if ($data === null) {
            return $response->withStatus(StatusCode::METHOD_NOT_ALLOWED);
        }

        if (!empty($data)) {
            return $response->withJson(json_decode((string)$data->getBody(), true));
        }
        return $response->withStatus(StatusCode::NO_CONTENT);
    }

    /**
     * @param string $method
     * @param array $args
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function processTypeformProxy(string $method, array $args): ?ResponseInterface {
        switch ($method) {

            case 'GET':
                if (empty($args['id'])) {
                    return $this->typeformRequest('GET', [
                        'query' => [
                            'workspace_id' => self::JOB_TYPE_WORKSPACE,
                            'page_size' => self::JOB_TYPE_PAGE_SIZE,
                        ],
                    ]);
                }

                return $this->typeformRequest('GET', [], "/{$args['id']}");
            default:
                return null;
        }
    }

    /**
     * @param string $method
     * @param array $options
     * @param string $uri
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function typeformRequest(string $method, array $options = [], string $uri = ''): ResponseInterface {
        return $this->http->send(new GuzzleRequest($method, self::TYPEFORM_BASE_URL . $uri, [
            'Authorization' => "Bearer {$this->settings['typeform']['api']}"
        ]), $options);
    }
}