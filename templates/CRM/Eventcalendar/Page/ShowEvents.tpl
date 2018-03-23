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
   if(showTime == 0) {
     jQuery('#calendar').fullCalendar({
       events: events_data,
       eventRender: function(event, element) {
         jQuery(element).find(".fc-time").remove();
       }
     });
   }
   else {
     jQuery("#calendar").fullCalendar(events_data);
   }
 }
</script>
{/literal}
