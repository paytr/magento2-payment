<?php

namespace Paytr\Payment\Test\Unit\Block;

use Magento\Framework\App\ErrorHandler;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Paytr\Payment\Block\Redirect;
use Paytr\Payment\Helper\PaytrHelper;
use Paytr\Payment\Helper\PaytrRequestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RedirectTest extends TestCase
{
    private const TEMPLATE = __DIR__ . '/../../../view/frontend/templates/redirect.phtml';

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var PaytrHelper|Stub
     */
    private $paytrHelper;

    /**
     * @var PaytrRequestHelper|Stub
     */
    private $paytrRequestHelper;

    /**
     * @var Redirect
     */
    private $block;

    protected function setUp(): void
    {
        set_error_handler([new ErrorHandler(), 'handler']);

        $this->logger = $this->createMock(LoggerInterface::class);
        $escaper = $this->createStub(Escaper::class);
        $escaper->method('escapeUrl')->willReturnArgument(0);
        $context = $this->createStub(Context::class);
        $context->method('getLogger')->willReturn($this->logger);
        $context->method('getEscaper')->willReturn($escaper);

        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->paytrHelper = $this->createStub(PaytrHelper::class);
        $this->paytrRequestHelper = $this->createStub(PaytrRequestHelper::class);

        $this->block = new Redirect(
            $context,
            $this->messageManager,
            $this->paytrHelper,
            $this->paytrRequestHelper
        );
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    public function testTokenFailureIsLoggedAndRendersNoIframe(): void
    {
        $exception = new \Exception('PayTR get-token response could not be read');
        $order = $this->createStub(Order::class);
        $order->method('getRealOrderId')->willReturn('000000001');
        $this->paytrHelper->method('getOrder')->willReturn($order);
        $this->paytrRequestHelper->method('getPaytrToken')->willThrowException($exception);

        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $this->prepareLayout();
        $html = $this->render();

        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('paytriframe', $html);
    }

    public function testRendersIframeWithToken(): void
    {
        $order = $this->createStub(Order::class);
        $order->method('getRealOrderId')->willReturn('000000001');
        $this->paytrHelper->method('getOrder')->willReturn($order);
        $this->paytrRequestHelper->method('getPaytrToken')->willReturn('abc123');

        $this->logger->expects($this->never())->method('critical');
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->prepareLayout();
        $html = $this->render();

        $this->assertStringContainsString('src="https://www.paytr.com/odeme/guvenli/abc123"', $html);
        $this->assertStringContainsString("iFrameResize({}, '#paytriframe')", $html);
    }

    private function prepareLayout(): void
    {
        $method = new \ReflectionMethod(Redirect::class, '_prepareLayout');
        $method->invoke($this->block);
    }

    private function render(): string
    {
        $block = $this->block;
        ob_start();
        try {
            include self::TEMPLATE;
        } finally {
            $html = ob_get_clean();
        }

        return $html;
    }
}
