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
        targeted_input = jQuery('div.' + slug + ' fieldset#' + slug + '_' + form_num);
        jQuery('div.' + slug + ' span.' + form_num).toggle();
    } else if ( action == 'add' ) {
        targeted_input = jQuery('div.' + slug + ' fieldset.hidden.new').first();
        jQuery(targeted_input).toggleClass('new');
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).attr('disabled', false);
    } else if ( action == 'remove' ) {
        targeted_input = jQuery('div.' + slug + ' fieldset.expanded').first();
        jQuery(targeted_input).toggleClass('expanded');
        jQuery(targeted_input).toggleClass('new');
    }
    jQuery(targeted_input).toggle();
    var add_new = jQuery('a.add_new_link.' + slug);
    var remaining_fields = jQuery('div.' + slug + ' fieldset.new').length;
}

jQuery('a.toggle_link_manager').click( function() {
    toggle_link_field(this);
});

jQuery(document).ready(function($){
    $('select.multiple').multiSelect({
        selectableHeader: "<label>Click a post name to select it.</label>",
        selectionHeader: "<label>Click a post name to remove it.</label>",
    });
} );
