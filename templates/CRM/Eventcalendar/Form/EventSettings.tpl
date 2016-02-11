<div class="form-item">
	{crmScript ext=com.osseed.eventcalendar file=jscolor/jscolor.js}
	<div class="crm-block crm-form-block crm-event-setting-form-block">
		
		<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

		<table class="form-layout-compressed">
			<tr class="crm-event-extension-event_calendar_title">
				<td>&nbsp;</td>
				<td>{$form.event_calendar_title.label}<br />{$form.event_calendar_title.html} <br />
				<span class="description">{ts}Set title for event calendar.{/ts}</span></td>
			</tr>
			<tr class="crm-event-extension-show_end_date">
				<td>&nbsp;</td>
				<td>{$form.show_end_date.html} {$form.show_end_date.label}<br />
				<span class="description">{ts}Will show the event with start and end dates on calendar.{/ts}</span></td>
			</tr>
			<tr class="crm-event-extension-show_past_event">
				<td>&nbsp;</td>
				<td>{$form.show_past_event.html} {$form.show_past_event.label}<br />
				<span class="description">{ts}Will Show the Past events also.{/ts}</span></td>
			</tr>
			<tr class="crm-event-extension-event_is_public">
				<td>&nbsp;</td>
				<td>{$form.event_is_public.html} {$form.event_is_public.label}<br />
				<span class="description">{ts}Will show the event which are public.{/ts}</span></td>
			</tr>
			<tr class="crm-event-extension-events_event_month">
				<td>&nbsp;</td>
				<td>{$form.events_event_month.html} {$form.events_event_month.label}<br />
				<span class="description">{ts}Will show month parameter on calendar.{/ts}</span></td>
			</tr>
			<tr class="crm-event-extension-show_event_from_month">
				<td>&nbsp;</td>
				<td>{$form.show_event_from_month.html}<br /> {$form.show_event_from_month.label}<br />
				<span class="description">{ts}Will filter the events with start date less then defined number of months from current month.{/ts}</span></td>
			</tr>
			{foreach from=$event_type item="eventname" key="key"}
				{assign var="eventtype_variable" value="eventtype_$key"}
				{assign var="eventcolor_variable" value="eventcolor_$key"}
				<tr class="crm-event-extension-{$label}">
					<td>&nbsp;</td>
					<td>{$form.$eventtype_variable.html}&nbsp;{$form.$eventtype_variable.label}<br />{$form.$eventcolor_variable.html}</td>
				</tr> 
			{/foreach}
			<tr class="crm-event-extension-fullcalendarviews">
				<td>Calendar Views</td>
				<td>
					{foreach from=$fullcalendarviews item="viewName" key="view"}
						{assign var="view_variable" value="calendar_views_$view"}
						{$form.$view_variable.html}
						{$form.$view_variable.label}
						<br>
					{/foreach}
					<span class="description">{ts}Will show theses views on the calendar in the top right.{/ts}</span>
				</td>
			</tr> 
		</table>
		<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
	</div>
</div>

{literal}
<script type="text/javascript">

cj(function(){
	
	{/literal}
		{foreach from=$event_type item="eventname" key="key"}
			{literal}
			showhidecolorbox({/literal}{$key}{literal}); 
			{/literal}
		{/foreach}
	{literal} 
	
	if(!cj("#events_event_month").is( ':checked')) {
		cj('.crm-event-extension-show_event_from_month').hide();
	}
	cj('.crm-event-extension-events_event_month').bind('click', function() {
		if(cj("#events_event_month").is( ':checked')) {
			cj('.crm-event-extension-show_event_from_month').show();
			cj('#show_event_from_month').val('');
		} else {
			cj('.crm-event-extension-show_event_from_month').hide();
		}
		});
	});
   
	function updatecolor(label,color) {
		cj('input[name="'+label+'"]').val( color );
	}

	function showhidecolorbox(event_id) {
		var m = "eventtype_".event_id; 
		var n = "eventcolor_".event_id; 
		if(!cj("#"+m).is( ':checked')) {
			cj("#"+n).hide();
		} else {
			cj("#"+n).show();
		}   
	}
	
</script>
{/literal}
