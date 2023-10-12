import {Controller} from '@hotwired/stimulus';
import Modal from 'bootstrap/js/src/modal';

export default class extends Controller {
    static targets = ['modal'];

    connect() {
        let accepted = localStorage.getItem('legal');
        if ((!accepted) || (accepted !== 'accepted')) {
            this.modal = new Modal(this.modalTarget);
            this.modal.show();
        }
    }

    reject() {
        window.location.href = 'about:blank';
    }

    accept() {
        localStorage.setItem('legal', 'accepted');
        this.modal.hide();
    }
}