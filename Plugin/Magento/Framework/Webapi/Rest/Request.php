<?php

namespace Paytr\Payment\Plugin\Magento\Framework\Webapi\Rest;

/**
 * Class Request
 *
 * @package Paytr\Payment\Plugin\Magento\Framework\Webapi\Rest
 */
class Request
{

    /**
     * @param  \Magento\Framework\Webapi\Rest\Request $subject
     * @param  array                                  $result
     * @return array|string[]
     */
    public function afterGetAcceptTypes(\Magento\Framework\Webapi\Rest\Request $subject, array $result)
    {
        if ($subject->getRequestUri() === '/rest/V1/paytr/callback/' || $subject->getRequestUri() === '/index.php/rest/V1/paytr/callback/') {
            $result = ['text/html'];
        }
        return $result;
    }
}
