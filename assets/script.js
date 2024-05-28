/* This JavaScript code snippet is using jQuery to attach a click event handler to elements with the
classes 'close-btn' and 'nothanks-btn' within the document. When either of these elements is
clicked, the function provided will be executed. */
jQuery(document).on("click", ".close-btn, .nothanks-btn", function (event) {
	event.preventDefault ? event.preventDefault() : (event.returnValue = false);
	jQuery(".custom-model-pop").hide();
});
