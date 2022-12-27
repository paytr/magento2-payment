<?php

namespace Paytr\Payment\Webapi\Rest\Response\Renderer;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Response\RendererInterface;

/**
 * Class Html
 *
 * @package Paytr\Payment\Webapi\Rest\Response\Renderer
 */
class Html implements RendererInterface
{
    /**
     * @return string
     */
    public function getMimeType()
    {
        return 'text/html';
    }

    /**
     * @param  array|bool|float|int|object|string|null $data
     * @return string
     * @throws Exception
     */
    public function render($data)
    {
        if (is_string($data)) {
            return $data;
        } else {
            throw new Exception(
                __('Data is not html.')
            );
        }
    }
}
