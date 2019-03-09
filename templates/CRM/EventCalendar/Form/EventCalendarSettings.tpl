{* HEADER *}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}
      {if $descriptions.$elementName}<br /><span class="description">{$descriptions.$elementName}</span>{/if}
    </div>
    <div class="clear"></div>
  </div>
{/foreach}
<div class="crm-section">
    {if $descriptions.delete_warning}<br /><span id="delete_warning" class="description">{$descriptions.delete_warning}</span>{/if}
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
