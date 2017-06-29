<?php

namespace Reliv\RcmGoogleAnalytics\Controller;

use Reliv\RcmGoogleAnalytics\Entity\RcmGoogleAnalytics;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class VerificationController
 *
 * LongDescHere
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
class VerificationController extends AbstractActionController
{
    /**
     * get RcmGoogleAnalyticsService
     *
     * @return \Reliv\RcmGoogleAnalytics\Service\RcmGoogleAnalytics
     */
    protected function getRcmGoogleAnalyticsService()
    {
        return $this->getServiceLocator()->get(
            \Reliv\RcmGoogleAnalytics\Service\RcmGoogleAnalytics::class
        );
    }

    /**
     * get Verification Code From Route Match
     *
     * @return mixed
     */
    protected function getVerificationCodeFromRoute()
    {
        return $this->getEvent()->getRouteMatch()->getParam('verificationCode');
    }

    /**
     * indexAction
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $requestVerificationCode = $this->getVerificationCodeFromRoute();

        $model = $this->getRcmGoogleAnalyticsService()
            ->getCurrentAnalyticEntityWithVerifyCode(
                $requestVerificationCode,
                new RcmGoogleAnalytics()
            );

        $verificationCode = $model->getVerificationCode();

        if (empty($verificationCode)) {
            $response = $this->getResponse();
            $response->setStatusCode(404);

            return $response;
        }

        $view = new ViewModel(['model' => $model]);

        $view->setTerminal(true);

        return $view;
    }
}
