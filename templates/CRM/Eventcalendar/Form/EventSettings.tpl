{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* this template is used for adding/editing location type  *}

<div class="form-item">
{crmScript ext=com.osseed.eventcalendar file=jscolor/jscolor.js}
<fieldset><legend>
{ts}Events Calendar Settings{/ts}
</legend>
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
      {foreach from=$event_type item="label" key="eventname"}
     <tr class="crm-event-extension-{$label}">
    		<td>&nbsp;</td>
    		<td>{$form.$eventname.html}&nbsp;{$form.$eventname.label}<br />{$form.$label.html}</td>
    	</tr> 
     {/foreach}
  
 </table>
 
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>

{literal}

<script type="text/javascript">

cj( function( ) {
   {/literal}{foreach from=$show_hide_color item=memType key=opId}{literal}
   var event_id = {/literal}{$opId}{literal}
   showhidecolorbox(event_id); 
   {/literal}{/foreach}{literal} 
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
   
 function updatecolor(label,color)
{
cj('input[name="'+label+'"]').val( color );
}
function showhidecolorbox(event_id) 
{
 var n = "eventcolorid_".concat(event_id); 
 var m = "event_".concat(event_id); 
 if(!cj("#"+m).is( ':checked')) {
    cj("#"+n).hide();
  }
   else {
    cj("#"+n).show();
   }   
  
}
</script>
{/literal}
