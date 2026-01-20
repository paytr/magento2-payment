<?php

namespace Paytr\Payment\Webapi\Rest\Response\Renderer;

use Magento\Framework\Webapi\Rest\Response\RendererInterface;

/**
 * Class All
 *
 * @package Paytr\Payment\Webapi\Rest\Response\Renderer
 */
class All implements RendererInterface
{
    /**
     * Mime type.
     */
    private $mimeType;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $encoder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Json\Encoder $encoder
     */
    public function __construct(\Magento\Framework\Json\Encoder $encoder)
    {
        $this->encoder = $encoder;
        $this->mimeType = 'application/json';
    }

    /**
     * Convert data to JSON.
     *
     * @param object|array|int|string|bool|float|null $data
     * @return string
     */
    public function render($data)
    {
        if(is_string($data) && str_starts_with($data, 'RESTPTR-')) {
            $this->mimeType = 'text/plain';
            $result = str_replace('RESTPTR-', '', $data);
            return $result;
        }
        return $this->encoder->encode($data);
    }

    /**
     * Get JSON renderer MIME type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}
