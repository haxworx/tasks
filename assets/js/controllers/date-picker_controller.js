import 'flatpickr/dist/flatpickr.css';
import 'flatpickr/dist/themes/airbnb.css';

import {Controller} from '@hotwired/stimulus';
import flatpickr from "flatpickr";


export default class extends Controller {
    connect() {
        let elements = document.querySelectorAll('[data-date-picker-options]')

        elements.forEach(function (element) {
            flatpickr(element, JSON.parse(element.dataset.datePickerOptions));
        })
    }
}
