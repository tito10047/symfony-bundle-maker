<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\ControllersFeature;
use Tito10047\BundleMaker\Feature\TwigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$twig = $blueprint->hasFeature(TwigFeature::NAME);
$imports = $helper->imports([
    $naming->namespace.'\Exception\InvalidArgumentException',
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\HttpFoundation\Response',
    'Symfony\Component\HttpKernel\Exception\BadRequestHttpException',
    ...($twig ? ['Twig\Environment'] : []),
], $naming->namespace.'\Controller');
?>
<?= "<?php\n" ?>

namespace <?= $naming->namespace ?>\Controller;

<?= $imports->render() ?>

/**
<?php if ($twig): ?>
 * Greets people on a page rendered from the "<?= TwigFeature::templateName($naming) ?>" template.
<?php else: ?>
 * Greets people, in plain text.
<?php endif ?>
 *
 * Routed by the "<?= ControllersFeature::routeName($naming) ?>" route.
 */
final readonly class HelloController
{
    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
<?php if ($twig): ?>
        private <?= $imports->name('Twig\Environment') ?> $twig,
<?php endif ?>
    ) {
    }

    public function __invoke(string $name): Response
    {
        try {
            $greeting = $this->greeter->greet($name);
        } catch (<?= $imports->name($naming->namespace.'\Exception\InvalidArgumentException') ?> $exception) {
            throw new <?= $imports->name('Symfony\Component\HttpKernel\Exception\BadRequestHttpException') ?>($exception->getMessage(), $exception);
        }

<?php if ($twig): ?>
        return new Response($this->twig->render(<?= $helper->php(TwigFeature::templateName($naming)) ?>, [
            'greeting' => $greeting,
        ]));
<?php else: ?>
        return new Response($greeting, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
<?php endif ?>
    }
}
