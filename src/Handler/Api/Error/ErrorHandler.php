<?php

namespace Iwgb\Join\Handler\Api\Error;

use Aura\Session\Session as SessionManager;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Handler\RootHandler;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ErrorHandler extends RootHandler {

    private const FATAL_ERROR_RETURN_URL = 'https://iwgb.org.uk/error';
    private const SESSION_ERROR_RETURN_URL = 'https://iwgb.org.uk/join';

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $code = $args['code'] ?? 90;
        $error = Error::search((int)$code ?? 90);

        if ($error === false) {
            $code = 90;
            $error = Error::UNKNOWN()->getKey();
        }

        $type = $code < 50
            ? 'session'
            : 'fatal';

        return $response->withJson([
            'code'  => $code,
            'error' => strtolower($error),
            'type'  => $type
        ]);
    }

    public static function redirect(
        Response $response,
        SessionManager $sm,
        ?Error $error = null,
        ?Applicant $applicant = null
    ): ResponseInterface {

        $sm->destroy();

        $code = ($error ?? Error::UNKNOWN())->getValue();

        $queryData['code'] = $code;

        if (!empty($applicant)) {
            $queryData['aid'] = $applicant->getId();
        }

        $url = $code < 50
            ? self::SESSION_ERROR_RETURN_URL
            : self::FATAL_ERROR_RETURN_URL;

        return $response->withRedirect($url . '?' . http_build_query($queryData));
    }

    public static function redirectPreSession(Response $response) {
        return $response->withRedirect(self::FATAL_ERROR_RETURN_URL . '?' . http_build_query([
            'code' => Error::FATAL()->getValue(),
        ]));
    }
}