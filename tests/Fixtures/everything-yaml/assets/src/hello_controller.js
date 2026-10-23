import { Controller } from '@hotwired/stimulus';

/*
 * Closes the element it is attached to, e.g. a greeting:
 *
 *     <section {{ stimulus_controller('tito10047/everything-yaml-bundle/hello') }}>
 *         <button type="button" {{ stimulus_action('tito10047/everything-yaml-bundle/hello', 'close') }}>Close</button>
 *     </section>
 *
 * Before closing, it dispatches the "tito10047--everything-yaml-bundle--hello:close" event,
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
