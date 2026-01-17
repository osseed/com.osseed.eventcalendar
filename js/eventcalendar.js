function showhidecolorbox(event_id) {
  var n = "eventcolorid_" + event_id;
  var m = "event_" + event_id;
  if(!cj("#"+m).is( ':checked')) {
    cj("#"+n).parents('.crm-section').hide();
  }
  else {
    cj("#"+n).parents('.crm-section').show();
  }
}

CRM.$(function ($) {
  // Prefix '#' and capitalize on load
  $('input.color.crm-form-text[data-coloris]').each(function () {
    if (this.value) {
      if (!this.value.startsWith('#')) {
        this.value = '#' + this.value;
      }
      this.value = this.value.toUpperCase();
    }
  });

  // Initialize Coloris
  Coloris.setInstance('.color.crm-form-text[data-coloris]', {
    theme: 'default',
    themeMode: 'light',
    format: 'hex',
    focusInput: true,
    swatches: [
      '#4E79A7', '#F28E2B', '#59A14F',
      '#E15759', '#EDC948', '#B07AA1',
    ],
    alpha: false
  });

  // Add '#' and capitalize on focus
  $('input.color.crm-form-text[data-coloris]').on('focus', function () {
    if (this.value && !this.value.startsWith('#')) {
      this.value = '#' + this.value;
    }
    this.value = this.value.toUpperCase();
  });

  // Show/hide based on checkbox
  $('input[id^=event_]').each(function () {
    var event_id = this.id.replace('event_', '');
    var colorInput = $("#eventcolorid_" + event_id);
    var checkbox = $("#event_" + event_id);
    if (!checkbox.is(':checked')) {
      colorInput.parents('.crm-section').hide();
    } else {
      colorInput.parents('.crm-section').show();
    }
  });
});
