<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Iwgb\Join\Handler;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Guym4c\Airtable\AirtableApiException;
use Iwgb\Join\Log\Event;
use Iwgb\Join\Middleware\ApplicantSession;
use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallBranch extends RootHandler {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant($request);
        $applicant->setCoreDataComplete(true);

        $branch = $this->airtable->get('Branches', $applicant->getBranch());
        $plan = $this->airtable->get('Plans', $applicant->getPlan());

        $this->log->addInfo(Event::REDIRECT_TO_BRANCH, [
            'applicant' => $applicant->getId(),
            'h_branch' => $branch->Name,
            'branch' => $branch->getId(),
        ]);

        $this->em->flush();

        if (empty($branch->{'Typeform ID'})) {
            return $this->redirectToRoute($response, Route::CREATE_PAYMENT);
        }

        return self::redirectToTypeform($branch->{'Typeform ID'}, $request, $response, [
            'amount' => $plan->Amount,
        ]);
    }
}