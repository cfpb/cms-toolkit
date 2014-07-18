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
    var slug_index = cssClass.indexOf(' ') + 1;
    var id = cssClass.substr(slug_index);
    var slug_base = id.substr(0, id.length - 1);
    var current_field = cssClass.substr(-1);
    var next_field = parseInt(current_field) + 1;
    var field_slug = slug_base + next_field;
    var element = jQuery('#' + id );
    element.toggleClass('hidden');
    element.removeProp('disabled');
    jQuery(this).toggleClass(id);
    jQuery(this).toggleClass(field_slug);
    if ( element.length ===  0 ) {
        var total = jQuery(element).prop('class');
        console.log(total);
    }
});

jQuery(document).ready(function($){
    $('select.multiple').multiSelect({
        selectableHeader: "<label>Click a post name to select it.</label>",
        selectionHeader: "<label>Click a post name to remove it.</label>",
    });
} );