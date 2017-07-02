function handleSubmission(form) {
  var removeErrorClass = function(evt) {
    jQuery(evt.target).parents('.form-group').removeClass('has-error');
  };

  if (form.elements['preferred_method'].value === 'phone' && !form.elements['phone'].value) {
    alert('Phone can not enter if your preferred method is phone');
    jQuery(form.elements['phone']).parents('.form-group').addClass('has-error');
    form.elements['phone'].addEventListener('focus', removeErrorClass, { once: true });
    return false;
  }
  if (form.elements['invoice'].value.length !== 0 && form.elements['invoice'].value.length !== 10) {
    alert('Invoice must be exactly 10 characters in length');
    jQuery(form.elements['invoice']).parents('.form-group').addClass('has-error');
    form.elements['invoice'].addEventListener('focus', removeErrorClass, { once: true });
    return false;
  }
  var formJson = {};
  for(var i = 0; i < form.length; i++) {
    var e = form.elements[i];
    formJson[e.name] = e.value;
  }
  jQuery.post('/submit', formJson, 'json')
    .done(function(data) {
      alert(data.message);
      form.reset();
      jQuery('#enquiry-dialog').modal('hide');
    })
    .fail(function(data) {
      alert(data.message);
    });
  return false;
}