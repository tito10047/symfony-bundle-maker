import { Controller } from '@hotwired/stimulus';

/*
 * Closes the element it is attached to, e.g. a greeting:
 *
 *     <section {{ stimulus_controller('acme/everything-php-bundle/hello') }}>
 *         <button type="button" {{ stimulus_action('acme/everything-php-bundle/hello', 'close') }}>Close</button>
 *     </section>
 *
 * Before closing, it dispatches the "acme--everything-php-bundle--hello:close" event,
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
