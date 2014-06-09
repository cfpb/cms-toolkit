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
