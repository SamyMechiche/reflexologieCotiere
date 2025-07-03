import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';

export default class extends Controller {
    connect() {
        const calendarEl = this.element;
        const calendar = new Calendar(calendarEl, {
            initialView: 'dayGridMonth'
        });
        calendar.render();
    }
} 