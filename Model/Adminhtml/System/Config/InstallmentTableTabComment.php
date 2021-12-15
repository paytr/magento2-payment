<?php

namespace Paytr\Payment\Model\Adminhtml\System\Config;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;

/**
 * Class InstallmentTableTabComment
 *
 * @package Paytr\Payment\Model\Adminhtml\System\Config
 */
class InstallmentTableTabComment implements CommentInterface
{

    /**
     * @param  string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        return __('You can manage the display of the installment table on the product detail page.');
    }
}
