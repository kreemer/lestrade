import { Controller } from '@hotwired/stimulus';
import Calendar from '@toast-ui/calendar';
import '@toast-ui/calendar/dist/toastui-calendar.min.css';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    static values = {
        api: String,
        date: String
    }

    #calendar;

    connect() {
        const options = {
            defaultView: 'week',
            timezone: {
                zones: [
                    {
                        timezoneName: 'Europe/Zurich',
                        displayLabel: 'Zurich',
                    },
                ],
            },
            calendars: [
                {
                    id: 'calendar',
                    name: 'Frames',
                    backgroundColor: '#03bd9e',
                }
            ],
            week: { 
                eventView: ['time'],
                taskView: false,
                milestoneView: false,
                startDayOfWeek: 1
            },
            useFormPopup: false,
            useDetailPopup: true,
            usageStatistics: false,
            isReadOnly: true,

        };

        this.#calendar = new Calendar(this.element, options);
     
        this.#calendar.setDate(this.dateValue);
        this.update();
    }

    update() {
        fetch(this.apiValue + '?date=' + encodeURIComponent(this.dateValue))
            .then((response) => response.json())
            .then((data) => {
                this.#calendar.createEvents(data);
            });
    }
}
