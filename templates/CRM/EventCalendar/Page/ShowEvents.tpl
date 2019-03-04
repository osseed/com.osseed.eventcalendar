{if $eventTypes == TRUE}
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
 if (typeof(jQuery) != 'function')
     var jQuery = cj;
 cj( function( ) {
    buildCalendar( );
  });
 function buildCalendar( ) {
   var events_data = {/literal}{$civicrm_events}{literal};
   var jsonStr = JSON.stringify(events_data);
   var showTime = events_data.timeDisplay;

   jQuery('#calendar').fullCalendar({
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
      jQuery('#calendar').fullCalendar('rerenderEvents');
   })
 }
</script>
{/literal}
