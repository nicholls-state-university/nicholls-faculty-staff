jQuery(function () {
	jQuery(document).on('click', '.nicholls-fs-popup-close', function (e) {
		jQuery.magnificPopup.close();
	});
});

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
				jQuery( '.nicholls-fs-form-email-message' ).html( '<p></p>' );
				
				var the_link = jQuery( this.st.el ).attr('href'); 
				
				var the_address = the_link.replace( 'mailto:', '' );
				the_address = the_address.replace( '%40', '@' );
				
				var the_heading = '<strong>Send email to ' + '<a class="nicholls-fs-popup-close" href="mailto:' + the_address + '">' + the_address + '</a>' + '</strong>';
							
				jQuery( 'input[name="nicholls-fs-form-email-addr"]' ).val( the_address );
				jQuery( '#nicholls-fs-form-message-top' ).html( the_heading );	
				
			},
			beforeClose: function() {
				jQuery( '.nicholls-fs-form-email-message p' ).html( '' );
			}
		
		}
	});
	
	jQuery( ".nicholls-fs-form-email-ajax-image").hide();
	
	jQuery( "#nicholls-fs-form-email" ).submit( function(event) {
	
		event.preventDefault();	
		jQuery( ".nicholls-fs-form-email-ajax-image").show();		
		
		var posting = jQuery.ajax({
			type: 'POST',
			url: nicholls_fs_js_obj.ajaxurl,
			data: jQuery("#nicholls-fs-form-email :input").serialize(),
			beforeSend: function( xhr, options ) {
				if ( !jQuery( 'input[name="nicholls-fs-form-email-name"]' ).val() ) {
					jQuery(".nicholls-fs-form-email-message p").html('Please input your name.');
					jQuery( ".nicholls-fs-form-email-ajax-image").hide();
					xhr.abort();
				}
				if ( !jQuery( 'input[name="nicholls-fs-form-email-email"]' ).val() ) {
					jQuery(".nicholls-fs-form-email-message p").html('Please input your working email address.');
					jQuery( ".nicholls-fs-form-email-ajax-image").hide();
					xhr.abort();
				}
				if ( !jQuery( '#nicholls-fs-form-email-message' ).val() ) {
					jQuery(".nicholls-fs-form-email-message p").html('Please input your message.');
					jQuery( ".nicholls-fs-form-email-ajax-image").hide();
					xhr.abort();
				}							 
			},
			success: function( response ) {
				jQuery( ".nicholls-fs-form-email-ajax-image").hide();
				jQuery(".nicholls-fs-form-email-message p").html('Thanks. Your Message Sent Successfully. <a class="nicholls-fs-popup-close">[Close]</a>');
				console.log( response );
			},
			error: function( response ) {
				jQuery( ".nicholls-fs-form-email-ajax-image").hide();
				jQuery(".nicholls-fs-form-email-message p").html('Sorry, something went wrong. Please <a href="//www.nicholls.edu/contact">contact us</a>.' );
				console.log( response );
			},
			dataType: 'json'
		});
				
	} );
	
});