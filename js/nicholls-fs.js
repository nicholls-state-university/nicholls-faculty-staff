jQuery( document ).ready( function() {

	jQuery('.nicholls-fs-modal-email').magnificPopup({

          items: {
			  type: 'inline',
			  preloader: false,
			  midClick: true,
			  focus: '#name',
			  src: '#nicholls-fs-form-email'
          },

		// When elemened is focused, some mobile browsers in some cases zoom in
		// It looks not nice, so we disable it:
		callbacks: {
			beforeOpen: function() {
				this.st.focus = '#nicholls-fs-form-email-name';
				
				var the_link = jQuery( this.st.el ).attr('href'); 
				var the_address = the_link.replace( 'mailto:', '' );
				var the_heading = '<strong>Send email to ' + the_address + '</strong>';
							
				jQuery( 'input[name="nicholls-fs-form-email-addr"]' ).val( the_address );
				jQuery( '#nicholls-fs-form-message-top' ).html( the_heading );		
				
			}
		}
	});

	jQuery( ".nicholls-fs-form-email-ajax-image").hide();
	jQuery( "#nicholls-fs-form-email" ).submit( function(event) {
	
		jQuery( ".nicholls-fs-form-email-ajax-image").show();
		
		var posting = jQuery.post( nicholls_fs_js_obj.ajaxurl, jQuery("#nicholls-fs-form-email :input").serialize() ).done( function( response) {
			jQuery( ".nicholls-fs-form-email-ajax-image").hide();
			jQuery(".nicholls-fs-form-email-message p").html('Thanks. Your Message Sent Successfully.');
		}).fail( function() {
			jQuery( ".nicholls-fs-form-email-ajax-image").hide();
			jQuery(".nicholls-fs-form-email-message p").html('Sorry, something went wrong. Please <a href="//www.nicholls.edu/contact">contact us</a>.');
		});
		
		event.preventDefault();
	} );
	
});