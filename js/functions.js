function delete_form_data(slug, form_id) {
    var selectorBase = '#' + slug + '-set .set-input_' + form_id;
    // Clear text fields
    jQuery(selectorBase).each( function(index) {
        jQuery(this).val("");
        var iframe = '#' + this.id + '_ifr';
        var contents = jQuery(iframe).contents();
        var wysiwyg = contents.find("body");
        wysiwyg.html("");
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

function toggle_repeated_field(element) {
    var slug = jQuery(element).attr('data-term');
    var action = jQuery(element).attr('data-action-term');
    var form_id = jQuery(element).attr('data-term-id');
    var header = jQuery('#' + slug + '-header');
    var targeted_input = jQuery('#' + slug + '-set');
    if ( action == 'add') {
        // Enable and show the fieldset
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).toggleClass('hidden');
        jQuery(targeted_input).attr('disabled', false);

        // Hide the add link
        jQuery(element).toggleClass('hidden');
        jQuery(element).attr('disabled', true);
        
        // show the remove link
        var remove_link = jQuery("[data-term='" + slug +"'][data-action-term='remove']");
        jQuery(remove_link).toggleClass('hidden');
        jQuery(remove_link).attr('disabled', false);

        // Show the header
        jQuery(header).removeClass('hidden');

    } else if ( action == 'remove' ) {
        // Delete form data, and hide the fieldset
        delete_form_data(slug, form_id);
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).toggleClass('hidden');

        // Hide the remove link
        jQuery(element).toggleClass('hidden');
        jQuery(element).attr('disabled', true);

        // Show add link
        var add_link = jQuery("[data-term='" + slug +"'][data-action-term='add']");
        console.log(add_link.html());
        jQuery(add_link).toggleClass('hidden');
        jQuery(add_link).attr('disabled', false);

    }
}

jQuery('a.toggle-repeated-field').click( function() {
    toggle_repeated_field(this);
});

jQuery(document).ready(function($){
    $('select.multiple').multiSelect({
        selectableHeader: "<label>Click a post name to select it.</label>",
        selectionHeader: "<label>Click a post name to remove it.</label>",
    });
} );

jQuery('.tagdelbutton').click( function() {
    var taxonomy = jQuery(this).attr('id');
    var tagNum = jQuery(this).attr('data-term-tag-num');
    var term = jQuery(this).attr('data-term');
    jQuery('input[id=rm_' + taxonomy + '_' + tagNum + ']').val(term);
    jQuery(this).parent().html('');
});
jQuery('.filedelbutton').click( function() {
    var file_id = jQuery(this).attr('id');
    jQuery('input[id=rm_' + file_id + ']').val(file_id);
    jQuery(this).parent().html('');
});
