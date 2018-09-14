<?php

namespace Reliv\RcmGoogleAnalytics\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reliv\RcmGoogleAnalytics\Api\Analytics\GetCurrentAnalyticEntityWithVerifyCode;
use Reliv\RcmGoogleAnalytics\Entity\RcmGoogleAnalytics;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class VerificationView implements MiddlewareInterface
{
    protected $getCurrentAnalyticEntityWithVerifyCode;

    protected $templateFile;

    protected $model;

    /**
     * @param GetCurrentAnalyticEntityWithVerifyCode $getCurrentAnalyticEntityWithVerifyCode
     * @param string                                 $templateFile
     */
    public function __construct(
        GetCurrentAnalyticEntityWithVerifyCode $getCurrentAnalyticEntityWithVerifyCode,
        string $templateFile = __DIR__ . '/../../view/reliv/verification/index.phtml'
    ) {
        $this->getCurrentAnalyticEntityWithVerifyCode = $getCurrentAnalyticEntityWithVerifyCode;
        $this->templateFile = $templateFile;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $next
     *
     * @return ResponseInterface|HtmlResponse
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $requestVerificationCode = $request->getAttribute('verificationCode');

        $model = $this->getCurrentAnalyticEntityWithVerifyCode->__invoke(
            $request,
            $requestVerificationCode,
            new RcmGoogleAnalytics()
        );

        if (empty($model)) {
            return new HtmlResponse(
                '',
                404
            );
        }

        $verificationCode = $model->getVerificationCode();

        if (empty($verificationCode)) {
            return new HtmlResponse(
                '',
                404
            );
        }

        $this->model = $model;

        ob_start();
        include($this->templateFile);
        $content = ob_get_clean();

        return new HtmlResponse(
            $content
        );
    }
}
