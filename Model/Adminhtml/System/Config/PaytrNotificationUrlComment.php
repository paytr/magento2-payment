<?php

namespace Paytr\Payment\Model\Adminhtml\System\Config;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;

/**
 * Class PaytrNotificationUrlComment
 *
 * @package Paytr\Payment\Model\Adminhtml\System\Config
 */
class PaytrNotificationUrlComment implements CommentInterface
{

    /**
     * @param  string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        return __('Add the NOTIFICATION URL ADDRESS above to the <a href="https://www.paytr.com/magaza/ayarlar" target="_blank"> Notification URL </a> setting.');
    }
}
