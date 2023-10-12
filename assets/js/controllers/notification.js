import Alert from 'bootstrap/js/src/alert';

const MAXIMUM_ALERTS = 3;

export class Notification {
    constructor(message, warning = false) {
        this.warning = warning;
        this.messageText = message;
        this.message = {
            message: this.messageText,
        }
    }

    // Use Javascript to append Bootstrap 5 alert to our main content.
    show() {
        let container = document.querySelector('#main');
        let alerts = document.querySelectorAll('.alert');
        if ((alerts) && (alerts.length >= this.getMax())) {
            return;
        }
        let div = document.createElement('div');
        div.classList.add("alert", "alter-dismissible", "d-flex", 'align-items-center', "fade", "show");
        if (!this.warning) {
            div.classList.add('alert-info');
        } else {
            div.classList.add('alert-warning');
        }
        div.innerHTML = this.messageText;
        let button = document.createElement('button');
        button.setAttribute('data-bs-dismiss', 'alert');
        button.setAttribute('aria-label', 'Close');
        button.classList.add("btn-close", "text-right");
        div.prepend(button);
        container.prepend(div);
    }

    getMax() {
        return MAXIMUM_ALERTS;
    }
}