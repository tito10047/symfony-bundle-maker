<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\ControllersFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming     $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\Exception\InvalidArgumentException',
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\HttpFoundation\Response',
    'Symfony\Component\HttpKernel\Exception\BadRequestHttpException',
], $naming->namespace.'\Controller');
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>\Controller;

<?= $imports->render() ?>

/**
 * Greets people, in plain text.
 *
 * Routed by the "<?= ControllersFeature::routeName($naming) ?>" route.
 */
final readonly class HelloController
{
    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
    ) {
    }

    public function __invoke(string $name): Response
    {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (<?= $imports->name($naming->namespace.'\Exception\InvalidArgumentException') ?> $exception) {
            throw new <?= $imports->name('Symfony\Component\HttpKernel\Exception\BadRequestHttpException') ?>($exception->getMessage(), $exception);
        }

        return new Response($greeting, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
