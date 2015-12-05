<div id="calendar"></div>
{crmScript ext=com.osseed.eventcalendar file=fullcalendar.js}
{crmStyle ext=com.osseed.eventcalendar file=fullcalendar.css}
{crmStyle ext=com.osseed.eventcalendar file=civicrm_events.css}

{literal}
<script type="text/javascript">
 if (typeof(jQuery) != 'function')
     var jQuery = cj; 
 cj( function( ) {
    buildCalendar( );
  });
 function buildCalendar( ) {
  var events_data = {/literal}{$civicrm_events}{literal};
  var event_links_active = {/literal}{$event_links_active}{literal};
  if(event_links_active){
    events_data.eventMouseover = function(calEvent, domEvent) {
      var href = jQuery(this).attr('href');
      var eventID = getUrlParam(href, 'id');
      var layer = events_data.event_details_links[eventID];
		  jQuery(this).append(layer);
	   };
	  events_data.eventMouseout = function(calEvent, domEvent) {
		  jQuery('.event-info-details').remove();
	  };
  } 
  jQuery("#calendar").fullCalendar(events_data);		
 }

function getUrlParam(url, param) {
	var results = new RegExp('[\?&]' + param + '=([^&#]*)').exec(url);
  if (results==null){
  	return null;
  } else{
    return results[1] || 0;
  }
}
</script>
{/literal}
