function EnableSelections(id){
var inputSelect	= 'input[name=embeditor-choice-'+ id +']';
var heading		= 'input[name=embeditor-heading-'+id+']';
var source		= 'input[name=embeditor-source-'+id+']';
var text		= 'textarea[name=embeditor-text-'+id+']';
var size		= 'input[name=embeditor-size-'+id+']';

var full		= 'input[name=embeditor-width-'+id+']';
var img_s		= 'input.img_s_def_'+id+'';
var img_m		= 'input.img_m_def_'+id+'';
var img_l		= 'input.img_l_def_'+id+'';

	jQuery(document).ready(function($){
		// initial check to show or hide the fields based on selection
		jQuery('tr.embeditor-radio input:checked').each(function(index, element) {
			show_check = jQuery(this).val();
			console.log(show_check);
			if ( show_check == 'Yes') {
				jQuery('tr.embeditor-embed').show();
				jQuery('tr.embeditor-embed2').show();
				jQuery('tr.embeditor-embed3').show();
				jQuery('tr.embeditor-embed4').show();												
			}
			if ( show_check == 'No') {
				jQuery('tr.embeditor-embed').hide();
				jQuery('tr.embeditor-embed2').hide();
				jQuery('tr.embeditor-embed3').hide();
				jQuery('tr.embeditor-embed4').hide();												
			}

		});
		// toggle for fields when user makes a selection
		jQuery('tr.embeditor-radio input:radio').change(function() {
			show_check = jQuery(this).val();
			console.log(show_check);
			if ( show_check == 'Yes') {
				jQuery('tr.embeditor-embed').show();
				jQuery('tr.embeditor-embed2').show();
				jQuery('tr.embeditor-embed3').show();
				jQuery('tr.embeditor-embed4').show();												
			}
			if ( show_check == 'No') {
				jQuery('tr.embeditor-embed').hide();
				jQuery('tr.embeditor-embed2').hide();
				jQuery('tr.embeditor-embed3').hide();
				jQuery('tr.embeditor-embed4').hide();												
			}

		});

		jQuery('tr.image-size div.image-size-item input:radio').change(function() {
			small_val	= jQuery(img_s).val();
			medium_val	= jQuery(img_m).val();
			large_val	= jQuery(img_l).val();
			full_val	= jQuery(full).val();			
			nsize		= jQuery(this).val();

			if (nsize == 'full')
				jQuery(size).val(full_val);
			
			if (nsize == 'large')
				jQuery(size).val(large_val);

			if (nsize == 'medium')
				jQuery(size).val(medium_val);

			if (nsize == 'thumbnail')
				jQuery(size).val(small_val);

		});
	});
}