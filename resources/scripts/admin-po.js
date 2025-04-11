/**
 * Admin .js file
 */

jQuery(document).ready(function($) {
    $("#sortable-subpages").sortable({
        axis: "y",
        opacity: 0.7,
        update: function(event, ui) {
            const order = $(this).find('li').map(function() {
                return $(this).data('id');
            }).get().join(',');
            $("#subpage_order").val(order); 
        }
    }).disableSelection();
});