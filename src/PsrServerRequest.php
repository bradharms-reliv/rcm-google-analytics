<?php

namespace Reliv\RcmGoogleAnalytics;

use Zend\Diactoros\ServerRequestFactory;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class PsrServerRequest
{
    public static function get()
    {
        return ServerRequestFactory::fromGlobals();
    }
}
