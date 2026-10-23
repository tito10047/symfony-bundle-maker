<?php
use Tito10047\BundleMaker\Feature\UxFeature;

/**
 * @var Tito10047\BundleMaker\Naming\BundleNaming $naming
 */
$name = UxFeature::controllerName($naming);
?>
import { Controller } from '@hotwired/stimulus';

/*
 * Closes the element it is attached to, e.g. a greeting:
 *
 *     <section {{ stimulus_controller('<?= $name ?>') }}>
 *         <button type="button" {{ stimulus_action('<?= $name ?>', 'close') }}>Close</button>
 *     </section>
 *
 * Before closing, it dispatches the "<?= UxFeature::controllerIdentifier($naming) ?>:close" event,
 * which listeners can cancel with event.preventDefault().
 *
 * Plain JavaScript, served as is by AssetMapper: no build step.
 */
export default class extends Controller {
    close() {
        const event = this.dispatch('close', { cancelable: true });

        if (!event.defaultPrevented) {
            this.element.remove();
        }
    }
}
