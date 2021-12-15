<?php

namespace Paytr\Payment\Model\Adminhtml\System\Config;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;

/**
 * Class InstallmentTableShowAllComment
 *
 * @package Paytr\Payment\Model\Adminhtml\System\Config
 */
class InstallmentTableShowAllComment implements CommentInterface
{
    /**
     * @param  string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        return __('You can show all the installments in details or as a summary on the installment table on the product detail page.');
    }
}
