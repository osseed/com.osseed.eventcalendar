{if !empty($eventTypes) == TRUE}
  <select id="event_selector" class="crm-form-select crm-select2 crm-action-menu fa-plus">
    <option value="all">{ts}All{/ts}</option>
    {foreach from=$eventTypes item=type}
    <option value="{$type}">{$type}</option>
    {/foreach}
  </select>
{/if}
<div id="calendar"></div>
{literal}
<script type="text/javascript">
  if (typeof(jQuery) != 'function'){
    var jQuery = cj;
  }
  else {
    var cj = jQuery;
  }

  cj( function( ) {
    checkFullCalendarLIbrary()
    .then(function() {
      buildCalendar();
    })
    .catch(function() {
      alert('Error loading calendar, try refreshing...');
    });
  });

/*
 * Checks if full calendar API is ready.
 *
 * @returns {Promise}
 *  if library is available or not.
 */
function checkFullCalendarLIbrary() {
  return new Promise((resolve, reject) => {
    if(cj.fullCalendar) {
      resolve();
    } else {
      cj(document).ajaxComplete(function() {
        if(cj.fullCalendar) {
          resolve();
        } else {
          reject();
        }
      });
    }
  });
}

function buildCalendar( ) {
  var events_data = {/literal}{$civicrm_events}{literal};
  var jsonStr = JSON.stringify(events_data);
  var showTime = events_data.timeDisplay;
  var weekStartDay = {/literal}{$weekBeginDay}{literal};

  cj('#calendar').fullCalendar({
    eventSources: [
      { events: events_data.events,}
    ],
    displayEventEnd: true,
    displayEventTime: showTime ? 1 : 0,
    firstDay:weekStartDay,
    timeFormat: 'h(:mm)A',
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'month,agendaWeek,agendaDay'
    },

    eventRender: function eventRender( event, element, view ) {
      if(event.eventType && events_data.isfilter == "1" ) {
        return ['all', event.eventType].indexOf(cj('#event_selector').val()) >= 0
      }
    }
  });

  CRM.$(function($) {
    $("#event_selector").change(function() {
      cj('#calendar').fullCalendar('rerenderEvents');
    });
  });
}
</script>
{/literal}
