<?php

namespace Reliv\RcmGoogleAnalytics\View\Helper;

use Reliv\RcmGoogleAnalytics\Service\RcmGoogleAnalytics;
use Zend\View\Helper\AbstractHelper;

/**
 * Class RcmGoogleAnalyticsJsHelper
 *
 * RcmGoogleAnalyticsJs Helper
 *
 * PHP version 5
 *
 * @category  Reliv
 * @package   moduleNameHere
 * @author    James Jervis <jjervis@relivinc.com>
 * @copyright 2015 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: <package_version>
 * @link      https://github.com/reliv
 */
class RcmGoogleAnalyticsJsHelper extends AbstractHelper
{
    protected $templatePath = '/../../../view/';
    /**
     * @var array
     */
    protected $config;

    /**
     * @var RcmGoogleAnalytics
     */
    protected $rcmGoogleAnalyticsService;

    /**
     * __construct
     * @param array              $config
     * @param RcmGoogleAnalytics $rcmGoogleAnalyticsService
     */
    public function __construct(
        $config,
        RcmGoogleAnalytics $rcmGoogleAnalyticsService
    ) {
        $this->config = $config;
        $this->rcmGoogleAnalyticsService = $rcmGoogleAnalyticsService;
    }

    /**
     * __invoke
     *
     * @return string
     */
    public function __invoke()
    {
        if (!$this->config['use-analytics']) {
            return "";
        }

        $template = __DIR__ . $this->templatePath . $this->config['javascript-view'];

        $this->model = $this->rcmGoogleAnalyticsService->getCurrentAnalyticEntity(
            new \Reliv\RcmGoogleAnalytics\Entity\RcmGoogleAnalytics()
        );

        // @todo There might be a better way
        ob_start();
        include($template);
        $output = ob_get_clean();

        return $output;
    }
}
