<?php

namespace Reliv\RcmGoogleAnalytics\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reliv\RcmGoogleAnalytics\Api\Acl\IsAllowed;
use Reliv\RcmGoogleAnalytics\Api\Translate;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class RcmGoogleAnalyticsAdminView implements MiddlewareInterface
{
    protected $translate;
    protected $isAllowed;
    protected $templatePath;

    /**
     * @param Translate $translate
     * @param IsAllowed $isAllowed
     * @param string    $templateFile
     */
    public function __construct(
        Translate $translate,
        IsAllowed $isAllowed,
        string $templateFile = __DIR__ . '/../../view/reliv/rcm-google-analytics/index.phtml'
    ) {
        $this->translate = $translate;
        $this->isAllowed = $isAllowed;
        $this->templateFile = $templateFile;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface|HtmlResponse
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        if (!$this->isAllowed->__invoke($request)) {
            return new HtmlResponse(
                $this->translate->__invoke('Access Denied: Google Analytics'),
                401
            );
        }

        ob_start();
        $title = $this->translate->__invoke("Google Analytics Settings");
        include($this->templateFile);
        $content = ob_get_clean();

        return new HtmlResponse(
            $content
        );
    }
}
