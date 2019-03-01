{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
<div class="crm-section">
  <div class="label">{$form.create_new_calendar.label}</div>
  <div class="content">{$form.create_new_calendar.html}
    {if $descriptions.create_new_calendar}<br /><span class="description">{$descriptions.create_new_calendar}</span>{/if}
  </div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.edit_existing_calendar.label}</div>
  <div class="content">{$form.edit_existing_calendar.html}
    {if $descriptions.edit_existing_calendar}<br /><span class="description">{$descriptions.edit_existing_calendar}</span>{/if}
  </div>
  <div class="clear"></div>
</div>
{foreach from=$elementNames item=elementName}
  {if $elementName != 'create_new_calendar' && $elementName != 'edit_existing_calendar' && $elementName != 'delete_current_calendars'}
  <div class="crm-section toggle">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}
      {if $descriptions.$elementName}<br /><span class="description">{$descriptions.$elementName}</span>{/if}
    </div>
    <div class="clear"></div>
  </div>
  {/if}
{/foreach}
<div class="crm-section">
  <div class="label">{$form.delete_current_calendars.label}</div>
  <div class="content">{$form.delete_current_calendars.html}
    {if $descriptions.delete_current_calendars}<br /><span class="description">{$descriptions.delete_current_calendars}</span>{/if}
  </div>
  <div class="clear"></div>
</div>

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT)

  <div>
    <span>{$form.favorite_color.label}</span>
    <span>{$form.favorite_color.html}</span>
  </div>

{* FOOTER *}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
