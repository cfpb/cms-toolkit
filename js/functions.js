function clear_link_manager(id) {
    var inputs = jQuery('ol#link_manager > li input.' + id);
    var urlInputs = jQuery('ol#link_manager > li input.' + id + '[type=url]');
    var textInputs = jQuery('ol#link_manager > li input.' + id + '[type=text]');
    var labels = jQuery('ol#link_manager > li label.' + id);
    var span   = jQuery('ol#link_manager > li span.' + id);
    var spanText = span.text();
    var linkText = spanText.substr(11, spanText.indexOf('Link URL: ') - 11);
    var urlIndex = spanText.indexOf('Link URL: ') + 10;
    var endIndex = spanText.indexOf('.Click') - urlIndex;
    var url = spanText.substr(urlIndex, endIndex);
    jQuery(inputs).show();
    jQuery(urlInputs).val(url);
    jQuery(textInputs).val(linkText);
    labels.show();
    span.remove();
}

function toggle_link_field(element) {
    var classes = jQuery(element).attr('class').split(/\s+/);
    var form_num = classes[3];
    var action = classes[2];
    var slug = classes[1];
    var targeted_input;
    if ( action == 'edit' ) {
        targeted_input = jQuery('.link_manager.' + slug + ' fieldset#' + slug + '_' + form_num);
        jQuery('.link_manager.' + slug + ' .link-existing.' + form_num).toggle();
    } else if ( action == 'add' ) {
        targeted_input = jQuery('.link_manager.' + slug + ' fieldset.hidden.new').first();
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).attr('disabled', false);
    } else if ( action == 'remove' ) {
        targeted_input = jQuery('.link_manager.' + slug + ' fieldset.expanded').first();
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).toggleClass('new');
    }
    jQuery(targeted_input).toggle();
    var add_new = jQuery('a.add_new_link.' + slug);
    var remaining_fields = jQuery('.link_manager.' + slug + ' fieldset.new').length;
}

function toggle_fieldset_of_formset(element) {
    var classes = jQuery(element).attr('class').split(/\s+/);
    var input_num = classes[3];
    var action = classes[2];
    var slug = classes[1];
    var header = jQuery('div > h4#' + slug);
    var targeted_input = jQuery('div > fieldset#' + slug );
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
        var remove_link = jQuery("div > p > a." + slug + ".remove");
        jQuery(remove_link).toggleClass('hidden');
        jQuery(remove_link).attr('disabled', false);

        // Show the header
        jQuery(header).removeClass('hidden');

    } else if ( action == 'remove' ) {
        // Delete form data, disable, and hide the fieldset
        delete_form_data(input_num);
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).attr('disabled', true);
        jQuery(targeted_input).css(' display: none; ');
        jQuery(targeted_input).toggle();

        // Hide the remove link
        jQuery(element).toggleClass('hidden');
        jQuery(element).attr('disabled', true);

        // Show add link
        var add_link = jQuery("div > a." + slug + ".add");
        jQuery(add_link).toggleClass('hidden');
        jQuery(add_link).attr('disabled', false);

        // Hide the header
        jQuery(header).addClass('hidden');
    }
}

function delete_form_data(input_num) {
    jQuery( "*[class*='form-input_" + input_num + "\']" ).each(function(index){
        jQuery( this ).val("");
    });
    jQuery( "p > span[class=\"" + input_num + "\"]" ).parent().html("");
}

jQuery('a.toggle_link_manager').click( function() {
    toggle_link_field(this);
});
jQuery('a.toggle_form_manager').click( function() {
    toggle_fieldset_of_formset(this);
});

jQuery(document).ready(function($){
    $('select.multiple').multiSelect({
        selectableHeader: "<label>Click a post name to select it.</label>",
        selectionHeader: "<label>Click a post name to remove it.</label>",
    });
} );
