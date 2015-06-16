/**
 * Plugin Extra Fielder
 * @package Extra Fielder
 * @author Alex - Studio Portal30
 * @copyright Portal30 2011-2012 http://portal30.ru
 */

$(function () {
    $(document).on('change', 'select.has_childs', function(){

        var id = $(this).attr('id');
        var pDicId = $(this).attr('dic_id');
        var pDicValue = $(this).val();
        var x = $('input[name="x"][type="hidden"]').first().val();
        var childs = [];
        var all_child_vals = $(this).attr('all_child_vals');
        var ex_only = (all_child_vals == 1)? 0 : 1;

        var i = 0;

        $('.loading').remove();

        // Элементы, зависимые от данного
        $('select[parent_el="'+id+'"]').each(function(index) {
            var parent_val = $(this).attr('parent_val'),
                parent2_val = $(this).attr('parent2_val');

            if($(this).attr('parent_dic') == pDicId) {
                parent_val = pDicValue;
            } else if($(this).attr('parent2_dic') == pDicId) {
                parent2_val = pDicValue;
            }

            childs.push({
                id: $(this).attr('id'),
                dic_id: $(this).attr('dic_id'),
                parent1_value: parent_val,
                parent2_value: parent2_val
            });
            $(this).html('<option value="0">......</option>');
            $(this).attr('disabled', 'disabled');
            i++;
            var Parent = $(this).parent();

            var bgspan = $('<span>', { id: "loading" + i, class: "loading"}).css({'position': 'absolute'});
            bgspan.html('<img src="./images/spinner.gif" alt="loading"/>');
            Parent.append(bgspan).css('position', 'relative').css('opacity', 0.4);
        });

        if (i == 0) return false; // Не найдены зависимые словари

        $.post('index.php?e=dictionary&a=ajxGetChildValues',
            //{parent_dicId: pDicId, parent_dicValue: pDicValue, childs: childs, ex_only: ex_only, x: x },
            {childs: childs, ex_only: ex_only, x: x },
            function(data) {
                $('select[parent_el="'+id+'"]').each(function(index) {
                    $(this).html('');
                    $(this).attr('disabled', null);
                    $(this).parent().css('opacity', 1);
                });
                $('.loading').remove();

                if (data.error != ''){
                    alert(data.error);
                    return false;
                }

                jQuery.each(data.data, function(element, elData){
                    jQuery.each(elData, function(key, val){
                        var idx = key.replace(/_/g, '');
                        var option = '<option value="'+ idx +'">'+ val +'</option>';
                        $('#' + element).append(option);
                    });
                });
        }, 'json').fail(function() {
                $('select[parent_el="'+id+'"]').each(function(index) {
                    $(this).html('');
                    $(this).attr('disabled', null);
                    $(this).parent().css('opacity', 1);
                });
                $('.loading').remove();
                alert( "Ошибка загрузки данных" );
            });


    });
});
