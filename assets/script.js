jQuery(document).on('click', '.close-btn, .nothanks-btn', function (event) {
    event.preventDefault ? event.preventDefault() : (event.returnValue = false);
    jQuery('.custom-model-pop').hide();
});