<?php

namespace Reliv\RcmGoogleAnalytics\Middleware;

use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reliv\RcmGoogleAnalytics\Api\Acl\IsAllowed;
use Reliv\RcmGoogleAnalytics\Api\Analytics\GetAnalyticEntityForSite;
use Reliv\RcmGoogleAnalytics\Api\RcmGoogleAnalyticsToArray;
use Reliv\RcmGoogleAnalytics\Api\Site\GetCurrentSiteId;
use Reliv\RcmGoogleAnalytics\Api\Translate;
use Zend\Diactoros\Response\JsonResponse;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class ApiRcmGoogleAnalyticsFind implements MiddlewareInterface
{
    protected $entityManager;
    protected $getCurrentSiteId;
    protected $translate;
    protected $getAnalyticEntityForSite;
    protected $isAllowed;
    protected $rcmGoogleAnalyticsToArray;

    /**
     * @param EntityManager             $entityManager
     * @param GetCurrentSiteId          $getCurrentSiteId
     * @param Translate                 $translate
     * @param GetAnalyticEntityForSite $getAnalyticEntityForSite
     * @param IsAllowed                 $isAllowed
     * @param RcmGoogleAnalyticsToArray $rcmGoogleAnalyticsToArray
     */
    public function __construct(
        EntityManager $entityManager,
        GetCurrentSiteId $getCurrentSiteId,
        Translate $translate,
        GetAnalyticEntityForSite $getAnalyticEntityForSite,
        IsAllowed $isAllowed,
        RcmGoogleAnalyticsToArray $rcmGoogleAnalyticsToArray
    ) {
        $this->entityManager = $entityManager;
        $this->getCurrentSiteId = $getCurrentSiteId;
        $this->translate = $translate;
        $this->getAnalyticEntityForSite = $getAnalyticEntityForSite;
        $this->isAllowed = $isAllowed;
        $this->rcmGoogleAnalyticsToArray = $rcmGoogleAnalyticsToArray;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface|JsonResponse
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
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

        $currentSiteId = $this->getCurrentSiteId->__invoke($request);
        $entity = $this->getAnalyticEntityForSite->__invoke($currentSiteId);

        if (empty($entity)) {
            return new JsonResponse(
                [
                    'data' => null,
                    'code' => 404,
                    'message' => $this->translate->__invoke('Analytics do not exist for this site'),
                    'errors' => [],
                ],
                404
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
