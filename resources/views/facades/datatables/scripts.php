<?php

?>
<script type="text/javascript">

    function datatableLoad(uuid, page = null, pagination = null)
    {
        if(page === null){
            page = 1;
        }
        if(pagination === null){
            pagination = <?=$defaultPagination?>;
        }
        var route = '<?=route('core_facades.datatable_ajax')?>';
        var container = $(document).find('#'+uuid+'_container');
        if(page && pagination && container.length > 0){
            var current_html = container.html();

            container.empty().append(core_loader);

            $.ajax({
                url: route,
                dataType: 'JSON',
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                data: {
                    datatable_uuid: uuid,
                    page: page,
                    pagination: pagination
                }
            }).done(function(resp) {
                if(resp.status === 'ok') {
                    var view = resp.view;
                    if(view){
                        container.empty().append(view);
                    }
                } else {
                    $.LoadingOverlay("hide");
                    container.empty().append(current_html);
                    console.error(resp.message);
                }
                rebuild_vendors();
            }).fail(function (resp) {
                $.LoadingOverlay("hide");
                console.error('AjaxFail');
            });
        }
    }

    $(document).on('change','.datatable-container .pagination_select', function() {
        var pagination = $(this).val();
        var uuid = $(this).attr('data-uuid');
        datatableLoad(uuid, 1, pagination);
    });

</script>
<?php
    if($filterBuilder){
        $filterBuilder->scripts();
    }
?>