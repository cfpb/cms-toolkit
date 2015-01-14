function delete_form_data(slug, form_id) {
    var selectorBase = "*[id='" + slug + "_formset'] *[class*='form-input_" + form_id + "']";

    // Clear text fields
    jQuery(selectorBase).each( function(index) {
        jQuery(this).val("");
    });
    // Clear checkboxes & radio buttons
    jQuery(selectorBase + ':checked').each( function(index) {
        jQuery(this).prop("checked", false);
    });
    // Clear select fields
    jQuery(selectorBase + ':selected').each( function(index) {
        jQuery(this).prop("selected", false);
    });
}

function toggle_fieldset_of_formset(element) {
    var classes = jQuery(element).attr('class').split(/\s+/);
    var form_id = classes[3];
    var action = classes[2];
    var slug = classes[1];
    var header = jQuery('#' + slug + '_header');
    var targeted_input = jQuery('#' + slug + '_formset');
    if ( action == 'add') {
        // Enable and show the fieldset
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).attr('disabled', false);
        jQuery(targeted_input).toggle();

        // Hide the add link
        jQuery(element).toggleClass('hidden');
        jQuery(element).attr('disabled', true);
        
        // show the remove link
        var remove_link = jQuery("." + slug + ".remove");
        jQuery(remove_link).toggleClass('hidden');
        jQuery(remove_link).attr('disabled', false);

        // Show the header
        jQuery(header).removeClass('hidden');
    } else if ( action == 'remove' ) {
        // Delete form data, and hide the fieldset
        delete_form_data(slug, form_id);
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).css(' display: none; ');
        jQuery(targeted_input).toggle();

        // Hide the remove link
        jQuery(element).toggleClass('hidden');
        jQuery(element).attr('disabled', true);

        // Show add link
        var add_link = jQuery("." + slug + ".add");
        jQuery(add_link).toggleClass('hidden');
        jQuery(add_link).attr('disabled', false);

        // Hide the header
        jQuery(header).addClass('hidden');
    }
}

jQuery('a.toggle_form_manager').click( function() {
    toggle_fieldset_of_formset(this);
});

jQuery(document).ready(function($){
    $('select.multiple').multiSelect({
        selectableHeader: "<label>Click a post name to select it.</label>",
        selectionHeader: "<label>Click a post name to remove it.</label>",
    });
} );

jQuery('.datedelbutton').click( function() {
    var taxAndTagNum = jQuery(this).attr('id').split('-');
    var taxonomy = taxAndTagNum[0];
    var tagNum = taxAndTagNum[3];
    var term = jQuery(this).attr('class').split(' ')[1];
    jQuery('input[id=rm_' + taxonomy + '_' + tagNum + ']').val(term);

    jQuery(this).parent().html('');
});
