import {Controller} from '@hotwired/stimulus';
import Alert from 'bootstrap/js/src/alert';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        new Alert(this.element);
    };
}
