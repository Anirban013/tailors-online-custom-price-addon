 jQuery(document).ready(function($){
	jQuery("[name = 'product_customizer']").change(function(e){
        var ids = jQuery("[name = 'product_customizer']").val();
       
        for(var i=0; i < tailor_localized_data.all_customizer.length; i++){
        	if(ids == tailor_localized_data.all_customizer[i]['id']){
        		jQuery('#_regular_price').val(parseInt(tailor_localized_data.original_price) + parseInt(tailor_localized_data.all_customizer[i]['price']));
        	}
        }
    });
 });