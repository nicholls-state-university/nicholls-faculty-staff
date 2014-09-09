jQuery( document ).ready( function() {
	jQuery( ".nicholls-fs-form-email-ajax-image").hide();
	jQuery( "#nicholls-fs-form-email" ).submit( function(event) {
		jQuery( ".nicholls-fs-form-email-ajax-image").show();
		var posting = jQuery.post( SCF.ajaxurl, jQuery("#nicholls-fs-form-email :input").serialize() ).done( function() {
			jQuery( ".nicholls-fs-form-email-ajax-image").hide();
			jQuery(".nicholls-fs-form-email-message p").html('Thanks. Your Message Sent Successfully.');
		}).fail( function() {
			jQuery( ".nicholls-fs-form-email-ajax-image").hide();
			jQuery(".nicholls-fs-form-email-message p").html('Oops, something went wrong.');
		});
		event.preventDefault();
	} );
});