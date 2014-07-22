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

jQuery('a.add_a_link').click(function($) {
    var cssClass = jQuery(this).prop('class');
    var classes = cssClass.split(/\s+/);
    var next_id = parseInt(classes[2], 10);
    var slug_base = classes[1];
    var next_field = slug_base + '_' + next_id;
    console.log(next_field);
    console.log(next_id);
    var element = jQuery('#' + next_field );
    var hidden_fieldsets = jQuery('fieldset.' + slug_base + '.hidden');
    element.toggleClass('hidden');
    element.removeProp('disabled');
    var max = jQuery('div.link_manager.' + slug_base).attr('class').split(/\s+/);
    var max_num = max[2].substr(4);
    jQuery(this).toggleClass(next_id.toString());
    next_id += 1;
    jQuery(this).toggleClass(next_id.toString());
    console.log(next_id >= parseInt(max_num, 10));
    if ( next_id > parseInt(max_num, 10) ) {
        jQuery(this).addClass('disabled');
    }
});

jQuery('a.toggle_link_manager').click( function($) {
    var classes = jQuery(this).attr('class').split(/\s+/);
    var counter = classes[1];
    var post_id = classes[2];
    var slug = classes[3];
    var slug_id = slug + '_' + counter;
    var targeted_input = jQuery('fieldset#' + slug_id);
    var targeted_form = jQuery('div.link_manager.' + slug + ' p');
    var current_id = jQuery('a.add_a_link.' + slug).prop('class').split(/\s+/)[2];
    var max = jQuery('div.link_manager.' + slug).attr('class').split(/\s+/)[2].substr(4);
    jQuery('a.add_a_link.' + slug).toggleClass(current_id);
    new_id = current_id - 1;
    jQuery('a.add_a_link.' + slug).toggleClass(new_id.toString());
    jQuery(targeted_form[counter]).toggleClass('hidden');
    jQuery(targeted_form[counter]).toggleClass(slug_id);
    jQuery(targeted_input).toggleClass('hidden');
    if ( jQuery('a.add_a_link.'+slug).hasClass('disabled') ) {
        jQuery('a.add_a_link.'+slug).removeClass('disabled');
        jQuery('a.add_a_link.'+slug).prop('disabled', 'false');
    }
});

jQuery(document).ready(function($){
    $('select.multiple').multiSelect({
        selectableHeader: "<label>Click a post name to select it.</label>",
        selectionHeader: "<label>Click a post name to remove it.</label>",
    });
} );