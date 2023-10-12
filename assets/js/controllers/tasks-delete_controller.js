import {Controller} from '@hotwired/stimulus';
import Modal from 'bootstrap/js/src/modal';
import {Notification} from './notification.js';

export default class extends Controller {
    static targets = ['modal', 'confirm', 'button'];
    static values = {
        token: String,
        task: String,
    }

    connect() {
    }

    confirm(event) {
        event.preventDefault();
        this.modal = new Modal(this.modalTarget);
        let confirmText = this.confirmTarget;
        document.addEventListener('keyup', () => {
            if (confirmText.value === 'delete me') {
                this.buttonTarget.classList.remove('disabled');
            } else {
                this.buttonTarget.classList.add('disabled');
            }
        })
        this.modal.show();
        this.buttonTarget.addEventListener('click', () => {
            this.TaskRemove();
        });
    }

    TaskRemove() {
        this.modal.hide();
        fetch('/tasks/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=' + this.tokenValue + '&task=' + this.taskValue,
        }).then(response => {
            if (!response.ok) {
                let notification = new Notification('A network error occurred', true);
                notification.show();
                return;
            }
            window.location.href = '/tasks';
        })
        .catch((error) => {
            let notification = new Notification('An error has occurred', true);
            notification.show();
            console.error('Error:', error);
        });
    }
}
