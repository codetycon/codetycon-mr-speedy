// alert();

jQuery(window).load(function() {
// jQuery('<div class="codetycon_loader spinner">loading</div>').prependTo('body');

})


function mr_speedy_event_handlar(order_id){
  // jQuery(".codetycon_loader.spinner").show();
  var data = {
			'action': 'codetycon_place_parcl_on_mr_speedy',
			'order_id': order_id
		};

jQuery.post(ajaxurl, data, function(response) {
	response = JSON.parse(response);
	var message = '<p>'+response.message+'</p>';
	if(!response.success){
		// jQuery('#codetycon_mr_spedy_popup').html(message);
		swal ( "Oops" ,  response.message ,  "error" );
	}else{
		if(response.hasOwnProperty('data')){
			message = '<div><table class="wp-list-table widefat fixed striped posts"><tr><th colspan="2">Your Order Info:</th></tr><tr><th>Status</th><th>'+response.data.status+'</th></tr><tr><th>Courier</th><th>'+response.data.courier+'</th></tr></table></div>';
		    jQuery('#codetycon_mr_spedy_popup').html(message);
	        // tb_show("Mr Speedy", "#TB_inline?inlineId=codetycon_mr_spedy_popup&width=200&height=200");
	        swal ({
			  title: "Your Order Status!",
			  text: response.data.status,
			  icon: "success",
			});


		}else{
		  // jQuery('#codetycon_mr_spedy_popup').html(message);
		  swal ({
			  title: "Success!",
			  text: response.message,
			  icon: "success",
			});

		}


		if(response.hasOwnProperty('status') && response.status=='placed' || 1){				
			jQuery('.codetycon-wc-action-button-'+order_id).removeClass('codetycon_mr_speedy_view').addClass('codetycon_mr_speedy_view_placed');
		}
	}
	
	// tb_show("Mr Speedy", "#TB_inline?inlineId=codetycon_mr_spedy_popup&width=200&height=200");
});
}