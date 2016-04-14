$(document).ready(function() {
    loadCalendar($('#demo-calendar-01'));
});

function loadCalendar(target){
    if( $.fn.fullCalendar ) {
        target.fullCalendar({
            header: {
                left: 'prev next today',
                center: 'title',
                right: 'prev next today'
            },
            editable: false,
            events: eventData,
            timezone: 'local'
        });

    }
}