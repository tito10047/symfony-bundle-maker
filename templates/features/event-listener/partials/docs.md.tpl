<?php
use Tito10047\BundleMaker\Contributor\Core\GreeterContributor;
use Tito10047\BundleMaker\Feature\EventListenerFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
?>
## Greeting Header

The bundle can add a greeting header to the responses of your application:

```http
HTTP/1.1 200 OK
<?= EventListenerFeature::headerName($naming) ?>: <?= GreeterContributor::DEFAULT_GREETING ?> World!
```

It is disabled by default. Enable it in the configuration of the bundle:

```yaml
# config/packages/<?= $naming->alias ?>.yaml
<?= $naming->alias ?>:
    greeting_header: true
```

The header is only added to the response of the main request, and a header with
the same name set by your application is kept as is. Run
`php bin/console debug:event-dispatcher kernel.response` to display the listener
among the other listeners of this event.
