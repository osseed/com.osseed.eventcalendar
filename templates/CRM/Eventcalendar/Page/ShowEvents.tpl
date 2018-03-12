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
  jQuery("#calendar").fullCalendar(events_data);		
 }
</script>
{/literal}
