<?php

namespace Reliv\RcmGoogleAnalytics\Api;

/**
 * @author James Jervis - https://github.com/jerv13
 */
interface Translate
{
    /**
     * @param string $message
     * @param array  $options
     *
     * @return mixed
     */
    public function __invoke(
        string $message,
        array $options = []
    ):string;
}
