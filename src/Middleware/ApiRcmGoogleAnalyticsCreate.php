<?php

namespace Reliv\RcmGoogleAnalytics\Middleware;

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reliv\RcmGoogleAnalytics\Api\Acl\IsAllowed;
use Reliv\RcmGoogleAnalytics\Api\RcmGoogleAnalyticsHydrate;
use Reliv\RcmGoogleAnalytics\Api\RcmGoogleAnalyticsToArray;
use Reliv\RcmGoogleAnalytics\Api\Site\GetCurrentSiteId;
use Reliv\RcmGoogleAnalytics\Api\Translate;
use Reliv\RcmGoogleAnalytics\Entity\RcmGoogleAnalytics;
use Reliv\RcmGoogleAnalytics\InputFilter\RcmGoogleAnalyticsFilter;
use Reliv\RcmGoogleAnalytics\Service\RcmGoogleAnalytics as RcmGoogleAnalyticsService;
use Zend\Diactoros\Response\JsonResponse;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class ApiRcmGoogleAnalyticsCreate
{
    protected $entityManager;
    protected $getCurrentSiteId;
    protected $translate;
    protected $rcmGoogleAnalyticsService;
    protected $isAllowed;
    protected $rcmGoogleAnalyticsHydrate;
    protected $rcmGoogleAnalyticsToArray;

    /**
     * @param EntityManager             $entityManager
     * @param GetCurrentSiteId          $getCurrentSiteId
     * @param Translate                 $translate
     * @param RcmGoogleAnalyticsService $rcmGoogleAnalyticsService
     * @param IsAllowed                 $isAllowed
     * @param RcmGoogleAnalyticsHydrate $rcmGoogleAnalyticsHydrate
     * @param RcmGoogleAnalyticsToArray $rcmGoogleAnalyticsToArray
     */
    public function __construct(
        EntityManager $entityManager,
        GetCurrentSiteId $getCurrentSiteId,
        Translate $translate,
        RcmGoogleAnalyticsService $rcmGoogleAnalyticsService,
        IsAllowed $isAllowed,
        RcmGoogleAnalyticsHydrate $rcmGoogleAnalyticsHydrate,
        RcmGoogleAnalyticsToArray $rcmGoogleAnalyticsToArray
    ) {
        $this->entityManager = $entityManager;
        $this->getCurrentSiteId = $getCurrentSiteId;
        $this->translate = $translate;
        $this->rcmGoogleAnalyticsService = $rcmGoogleAnalyticsService;
        $this->isAllowed = $isAllowed;
        $this->rcmGoogleAnalyticsHydrate = $rcmGoogleAnalyticsHydrate;
        $this->rcmGoogleAnalyticsToArray = $rcmGoogleAnalyticsToArray;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface|JsonResponse
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        if (!$this->isAllowed->__invoke($request)) {
            return new JsonResponse(
                [
                    'data' => null,
                    'code' => 401,
                    'message' => $this->translate->__invoke('Access Denied'),
                    'errors' => [],
                ],
                401
            );
        }

        $service = $this->rcmGoogleAnalyticsService;
        $currentSiteId = $this->getCurrentSiteId->__invoke($request);
        $entity = $service->getAnalyticEntityForSite($currentSiteId);

        if (!empty($entity)) {
            return new JsonResponse(
                [
                    'data' => $this->rcmGoogleAnalyticsToArray->__invoke($entity),
                    'code' => 400,
                    'message' => $this->translate->__invoke('Access Denied'),
                    'errors' => [],
                ],
                400
            );
        }

        $data = $request->getParsedBody();

        // INPUT VALIDATE
        $inputFilter = new RcmGoogleAnalyticsFilter();

        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'data' => $data,
                    'code' => 400,
                    'message' => $this->translate->__invoke('Analytics data is not valid'),
                    'errors' => $inputFilter->getMessages(),
                ],
                400
            );
        }

        $entity = new RcmGoogleAnalytics();

        $entity = $this->rcmGoogleAnalyticsHydrate->__invoke(
            $entity,
            $inputFilter->getValues()
        );

        $entity->setSiteId($currentSiteId);

        $entityManager = $this->entityManager;

        try {
            $entityManager->persist($entity);
            $entityManager->flush($entity);
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'data' => $this->rcmGoogleAnalyticsToArray->__invoke($entity),
                    'code' => 400,
                    'message' => $this->translate->__invoke('Analytics failed to save'),
                    'errors' => ['exception' => $exception->getMessage()],
                ],
                400
            );
        }

        return new JsonResponse(
            [
                'data' => $this->rcmGoogleAnalyticsToArray->__invoke($entity),
                'code' => 200,
                'message' => '',
                'errors' => [],
            ],
            200
        );
    }
}
