<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\EventListenerFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\HttpKernel\Event\ResponseEvent',
], $naming->namespace.'\EventListener');
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>\EventListener;

<?= $imports->render() ?>

/**
 * Adds a greeting header to the responses of the application, e.g. "<?= EventListenerFeature::headerName($naming) ?>: <?= GreeterContributor::DEFAULT_GREETING ?> World!".
 *
 * Listens to the "kernel.response" event when the "greeting_header" option is enabled.
 */
final readonly class GreetingHeaderListener
{
    public const HEADER = <?= $helper->php(EventListenerFeature::headerName($naming)) ?>;

    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
    ) {
    }

    public function onKernelResponse(<?= $imports->name('Symfony\Component\HttpKernel\Event\ResponseEvent') ?> $event): void
    {
        // the responses of sub-requests (fragments, ESI) are part of the response of the main request
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        // a header set by the application takes precedence
        if (!$headers->has(self::HEADER)) {
            $headers->set(self::HEADER, $this->greeter->greet('World'));
        }
    }
}
