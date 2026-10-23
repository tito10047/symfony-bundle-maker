<?php
/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\EventListener\GreetingHeaderListener',
    $naming->namespace.'\Greeter',
    'PHPUnit\Framework\TestCase',
    'Symfony\Component\HttpFoundation\Request',
    'Symfony\Component\HttpFoundation\Response',
    'Symfony\Component\HttpKernel\Event\ResponseEvent',
    'Symfony\Component\HttpKernel\HttpKernelInterface',
], $naming->testsNamespace.'\Unit\EventListener');
?>
<?= "<?php\n" ?>

namespace <?= $naming->testsNamespace ?>\Unit\EventListener;

<?= $imports->render() ?>

final class GreetingHeaderListenerTest extends TestCase
{
    public function testAddsTheGreetingHeaderToTheMainResponse(): void
    {
        $response = $this->dispatch(HttpKernelInterface::MAIN_REQUEST, new Response());

        $this->assertSame('Hi World!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    public function testIgnoresTheResponsesOfSubRequests(): void
    {
        $response = $this->dispatch(HttpKernelInterface::SUB_REQUEST, new Response());

        $this->assertFalse($response->headers->has(GreetingHeaderListener::HEADER));
    }

    public function testKeepsTheHeaderSetByTheApplication(): void
    {
        $response = $this->dispatch(HttpKernelInterface::MAIN_REQUEST, new Response(headers: [GreetingHeaderListener::HEADER => 'Welcome!']));

        $this->assertSame('Welcome!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    /**
     * @param HttpKernelInterface::*_REQUEST $requestType
     */
    private function dispatch(int $requestType, Response $response): Response
    {
        $listener = new GreetingHeaderListener(new Greeter('Hi'));

        $listener->onKernelResponse(new ResponseEvent($this->createStub(HttpKernelInterface::class), Request::create('/'), $requestType, $response));

        return $response;
    }
}
