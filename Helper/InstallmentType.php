<?php

namespace Paytr\Payment\Helper;

/**
 * Class InstallmentType
 *
 * @package Paytr\Payment\Helper
 */
class InstallmentType
{

    /**
     * @return string[]
     */
    public function getInstallmentTypes()
    {
        return [
            0 => __('All Installment Options'),
            1 => __('Single Payment'),
            2 => __('Up to 2 Installments'),
            3 => __('Up to 3 Installments'),
            4 => __('Up to 4 Installments'),
            5 => __('Up to 5 Installments'),
            6 => __('Up to 6 Installments'),
            7 => __('Up to 7 Installments'),
            8 => __('Up to 8 Installments'),
            9 => __('Up to 9 Installments'),
            10 => __('Up to 10 Installments'),
            11 => __('Up to 11 Installments'),
            12 => __('Up to 12 Installments'),
        ];
    }
}
