<?php

namespace Reliv\RcmGoogleAnalytics\Middleware;

use Interop\Container\ContainerInterface;
use Reliv\RcmGoogleAnalytics\Api\Analytics\GetCurrentAnalyticEntityWithVerifyCode;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class VerificationViewFactory
{
    /**
     * __invoke
     *
     * @param $container ContainerInterface|ServiceLocatorInterface
     *
     * @return VerificationView
     */
    public function __invoke($container)
    {
        return new VerificationView(
            $container->get(GetCurrentAnalyticEntityWithVerifyCode::class)
        );
    }
}
