<script type="text/javascript">
    var pokaz_filtry = '<?=get_string('show_filters', 'local_core_facades')?>';
    var ukryj_filtry = '<?=get_string('hide_filters', 'local_core_facades')?>';
    var filter_uuid = '<?=$uuid?>';
    var filter_id = '#' + filter_uuid;

    $(document).ready(function() {
        var current_state = $(filter_id).find('select, textarea, input:not([type=hidden])').serializeArray();

        if(current_state.length > 0){
            $(filter_id).find('.filter-submit').click();
        }
    });

    function filter_label()
    {
        $('.filter-container .btn-accordion').each(function() {
            var aria = $(this).attr('aria-expanded');
            var label = pokaz_filtry;
            if(aria === 'false'){
                label = ukryj_filtry;
            }

            $(this).empty().append(label);
        });
    }

    $('.filter-container .btn-accordion').on('click', function() {
        filter_label();
    });

    $(filter_id + ' .filter-cancel').on('click', function() {
        $('select, textarea, input:not([type=hidden])').each(function() {
            $(this).val('');
            if(this.localName === 'select'){
                $(this).trigger('chosen:updated');
            }
        });
        $(filter_id + ' .filter-submit').click();
    });

    $(filter_id + ' .filter-submit').on('click', function() {
        var datas = $(filter_id).find('select, textarea, input').serializeArray();
        var route = '<?=route('core_facades.process_filters')?>';

        $.ajax({
            url: route,
            dataType: 'JSON',
            async: true,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': csrf
            },
            data: datas
        }).done(function(resp) {
            if(resp.status === 'error') {
                console.error(resp.message);
            }
        }).fail(function (resp) {
            console.error('AjaxFail');
        });

        <?php

        if($onFilter){ 
            echo $onFilter;
        } ?>
    });
        
</script>