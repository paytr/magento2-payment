<?php

namespace Paytr\Payment\Api;

/**
 * Interface WebhookInterface
 *
 * @package Paytr\Payment\Api
 */
interface WebhookInterface
{
    /**
     * GET for Post api
     *
     * @return string
     */
    public function getResponse(): string;
}
