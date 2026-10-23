<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\DoctrineFeature;
use Tito10047\BundleMaker\Feature\SemanticConfigFeature;

/**
 * @var Tito10047\BundleMaker\Blueprint\BundleBlueprint $blueprint
 * @var Tito10047\BundleMaker\Naming\BundleNaming       $naming
 * @var Tito10047\BundleMaker\Template\TemplateHelper   $helper
 */
$entity = $naming->namespace.'\Entity\Greeting';
$imports = $helper->imports([
    $entity,
    GreeterContributor::interfaceName($naming),
    'Doctrine\ORM\EntityManagerInterface',
], 'App\Service');
?>
## Doctrine Entity

The bundle provides the `<?= $entity ?>` entity, stored in the
`<?= DoctrineFeature::tableName($naming) ?>` table. It is mapped when your application uses
[Doctrine ORM with DoctrineBundle](https://symfony.com/doc/current/doctrine.html):

```console
composer require symfony/orm-pack
```

The bundle maps its entities in the default entity manager of your application, whatever its
`auto_mapping` setting. Run `php bin/console doctrine:mapping:info` to check it, and create
the table as explained in the [installation instructions](../README.md#installation).

Use the entity like the ones of your application:

```php
namespace App\Service;

<?= $imports->render() ?>

final class GreetingRecorder
{
    public function __construct(
        private readonly GreeterInterface $greeter,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function greet(string $name): Greeting
    {
        $greeting = new Greeting($name, $this->greeter->greet($name));
        $this->entityManager->persist($greeting);
        $this->entityManager->flush();

        return $greeting;
    }
}
```

### Using Another Entity Manager

When your application has several entity managers, choose the one mapping the entities of the bundle:

```yaml
<?php if ($blueprint->hasFeature(SemanticConfigFeature::NAME)): ?>
# config/packages/<?= $naming->alias ?>.yaml
<?= $naming->alias ?>:
    model_manager_name: customer
<?php else: ?>
# config/services.yaml
parameters:
    <?= DoctrineFeature::managerParameter($naming) ?>: customer
<?php endif ?>
```

If another entity manager has `auto_mapping` enabled, it maps the entities of the bundle too.
Exclude them from it:

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        entity_managers:
            default:
                auto_mapping: true
                mappings:
                    <?= $naming->className ?>: false
```
