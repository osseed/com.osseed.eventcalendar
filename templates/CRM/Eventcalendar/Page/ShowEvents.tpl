<div id="calendarKey">
	{foreach from=$eventTypesColors item="event" key="id"}
		<span class="event-type-key" style="background: {$event.color}">{$event.name}</span>
	{/foreach}
</div>
<div class="full-calendar" data-events-data="{$civicrm_events|htmlspecialchars}"></div>
