jQuery( document ).ready( function() {

	jQuery('.popup-with-form').magnificPopup({
		type: 'inline',
		preloader: false,
		focus: '#name',

		// When elemened is focused, some mobile browsers in some cases zoom in
		// It looks not nice, so we disable it:
		callbacks: {
			beforeOpen: function() {
				if( jQuery(window).width() < 700 ) {
					this.st.focus = false;
				} else {
					this.st.focus = '#name';
				}
			}
		}
	});

	jQuery( ".nicholls-fs-form-email-ajax-image").hide();
	jQuery( "#nicholls-fs-form-email" ).submit( function(event) {
		jQuery( ".nicholls-fs-form-email-ajax-image").show();
		var posting = jQuery.post( nicholls_fs_js_obj.ajaxurl, jQuery("#nicholls-fs-form-email :input").serialize() ).done( function() {
			jQuery( ".nicholls-fs-form-email-ajax-image").hide();
			jQuery(".nicholls-fs-form-email-message p").html('Thanks. Your Message Sent Successfully.');
		}).fail( function() {
			jQuery( ".nicholls-fs-form-email-ajax-image").hide();
			jQuery(".nicholls-fs-form-email-message p").html('Oops, something went wrong.');
		});
		event.preventDefault();
	} );

});