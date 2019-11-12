if (typeof(jQuery) != 'function')
  var jQuery = cj;

cj(function(){
  buildCalendar();
});

function buildCalendar() {

  var events_data = cj('#calendar').data('events');
  var showTime = events_data.timeDisplay;

  cj('#calendar').fullCalendar({
    eventSources: [
      { events: events_data.events,}
    ],
    displayEventTime: showTime ? 1 : 0,
    timeFormat: 'h(:mm)A',

    eventRender: function eventRender( event, element, view ) {
      if(event.eventType && events_data.isfilter == "1" ) {
        return ['all', event.eventType].indexOf(cj('#event_selector').val()) >= 0
      }
    }
  });

  cj('#event_selector').on('change', function(){
    cj('#calendar').fullCalendar('rerenderEvents');
  })
}