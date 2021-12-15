<?php

namespace Paytr\Payment\Model\Adminhtml\System\Config;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;

/**
 * Class InstallmentTableTokenComment
 *
 * @package Paytr\Payment\Model\Adminhtml\System\Config
 */
class InstallmentTableTokenComment implements CommentInterface
{

    /**
     * @param  string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        return __('Go to the <a href="https://www.paytr.com/magaza/pft-ayar" target="_blank"> Settings Page </a> for the Installment Table. Paste the token code in this field.');
    }
}
