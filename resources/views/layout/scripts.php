<?php
global $CFG;
$assets_path = $CFG->wwwroot . '/assets/lib'; 
?>
<style>
  .core-loader {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    border: 2px solid;
    border-color: var(--primary) var(--primary) transparent transparent;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
    align-self: center;
    opacity: .75;
  }
  .core-loader::after,
  .core-loader::before {
    content: '';  
    box-sizing: border-box;
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
    border: 2px solid;
    border-color: transparent transparent var(--secondary) var(--secondary);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    box-sizing: border-box;
    animation: rotationBack 0.5s linear infinite;
    transform-origin: center center;
  }
  .core-loader::before {
    width: 32px;
    height: 32px;
    border-color: var(--primary) var(--primary) transparent transparent;
    animation: rotation 1.5s linear infinite;
  }
      
  @keyframes rotation {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  } 
  @keyframes rotationBack {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(-360deg);
    }
  }

  .core-overlay {
    box-sizing: border-box;
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: 2147483648;
    display: flex;
    flex-flow: column;
    align-items: center;
    justify-content: space-around;
    top:0;
    left:0;
    background: rgba(216, 222, 236,0.7);
    opacity: 1;
  }
  .core-overlay .core-loader {
    height: 65px;
    width: 65px;
  }
</style>
<script type="text/javascript" src="<?=$assets_path?>/choosen/chosen.jquery.js"></script>
<script type="text/javascript" src="<?=$assets_path?>/tippy/popper.min.js"></script>
<script type="text/javascript" src="<?=$assets_path?>/tippy/tippy-bundle.umd.min.js"></script>
<script type="text/javascript" src="<?=$assets_path?>/loadingoverlay/loadingoverlay.min.js"></script>
<script type="text/javascript" src="<?=$assets_path?>/moment/moment-with-locales.min.js"></script>
<script type="text/javascript" src="<?=$assets_path?>/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>
<script type="text/javascript" src="<?=$assets_path?>/trix-editor/trix.umd.min.js"></script>
<script type="text/javascript">

    var url = '<?=$CFG->wwwroot?>';
    var csrf = $('meta[name="csrf"]').attr('content');
    var sesskey = '<?=sesskey()?>';
    var core_loader = '<div class="col-md-12 text-center mt-2"><span class="core-loader"></span></div>';
    var error_ajax = '<?=get_string('error_ajax', 'local_core_facades')?>';
    var error_ajax_403 = '<?=get_string('error_ajax_403', 'local_core_facades')?>';
    var error_ajax_404 = '<?=get_string('error_ajax_404', 'local_core_facades')?>';
    var error_ajax_500 = '<?=get_string('error_ajax_500', 'local_core_facades')?>';
    const swal_timer = 10000;
    var selectOptions = {
			width: '100%',
			allow_single_deselect: true,
			search_contains: true,
			no_results_text: '<?=get_string('no_datas','local_core_filter')?>',
			placeholder_text_single: '<?=get_string('choose','local_core_filter')?>',
            placeholder_text_multiple: '<?=get_string('choose','local_core_filter')?>'
		};
    
    function build_tippy(){
        $('body').find(".tippy").each(function (){
            var attribute = $(this).attr('data-tippy');
            var content = null;
            var title = $(this).attr('data-tippy-title');
			var placement = $(this).attr('data-tippy-placement');
            var element = this;
            if(title) {
                content = '<div class="text-primary fw-bold mb-1">'+title+'</div><div>'+attribute+'</div>';
            } else {
                content = attribute;
            }
			if(!placement) {
				placement = 'top';
			}

            if(content){
                var instance = tippy(element, {
                    content: content,
                    arrow: false,
                    allowHTML: true,
		            placement: placement
                });
                if(attribute=='') {
                    instance.destroy();
                }
            }
        });
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function rebuild_vendors()
    {
        build_tippy();
        $('.chosen').chosen(selectOptions); 
        $(".pagination_select").chosen(selectOptions);
    }

    // Usuwa z tablicy wskazaną wartość.
    function arr_remove(arr, item)
    {
        y = $.grep(arr, function(value) {
                return value != item;
        });
        return y;
    }

    function makeFormErrors(errors)
    {
        if(errors){
            Object.keys(errors).forEach(key => {
                var selector = '.form-input[name="'+key+'"]';
                if($(selector).length === 0){
                    selector = '.form-input[name="'+key+'[]"]';
                }
                var validation_selector = '#feedback_'+key;

                $(selector).addClass('is-invalid');
                $(validation_selector).empty();
                $(validation_selector).append(errors[key]);
                if($(validation_selector).is(':hidden')){
                    $(validation_selector).show();
                }
            });
        }
    }

    function clearAllErrors() {
        $(document).find('.form-input.is-invalid').each(function (){
            $(this).removeClass('is-invalid');
        });
        $(document).find('.invalid-feedback').each(function (){
            $(this).hide();
        });
    }

    function get_modal(route, datas)
    {
        if($("#modal_container").length && $("#modal_input").length){
            core_overlay();
            $.ajax({
                url: route,
                dataType: 'JSON',
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                data: datas, 
                statusCode: {
                    403: function() {
                        error(error_ajax_403, '<?=get_string('no_permission', 'local_core_facades')?>');
                    },
                    404: function() {
                        error(error_ajax_404);
                    },
                    500: function() {
                        error(error_ajax_500);
                    }
                }
            }).done(function(resp) {
                if(resp.status === 'ok') {
                    $("#modal_container").empty();
                    if($("#modal_container").append(resp.view)){
                        rebuild_vendors();
                        core_overlay();

                        $("#modal_input").click();
                    }
                } else {
                    core_overlay();
                    error(resp.message);
                }
            }).fail(function (resp) {
				core_overlay();
				console.error(error_ajax);
			});
        } else {
            console.error("#modal_container lub #modal_input nie istnieją. nie można pobrać modala.");
        }
    }

    function submit_form(form_id, route, _callback = null)
    {
        var inputs = $('#' + form_id + ' .form-input');
        if(inputs.length > 0){
            core_overlay();
            clearAllErrors();
            var datas = inputs.serializeArray();
            datas.push( { name: 'action', value: route } );
            $.ajax({
                url: '<?=request()->getPublic()?>',
                dataType: 'JSON',
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                data: datas
            }).done(function(resp) {
                if(resp.status === 'ok') {
                    core_overlay();

                    if(resp.redirect){

                        if(resp.message){
                            success(resp.message, null, function(result) {
                                if(result){
                                    core_overlay();
                                    window.location.href = resp.redirect;
                                }
                            });
                        } else {
                            window.location.href = resp.redirect;
                        }
                    } else {
                        if(resp.message){
                            success(resp.message, null);
                        } 
                    }
                    if(_callback){
                        _callback(resp.status);
                    }

                } else {
                    if(resp.errors){
                        makeFormErrors(resp.errors);
                    } else {
                        error(resp.message);
                    }
                    if(_callback){
                        _callback(resp.status);
                    }
                    core_overlay();
                }
            }).fail(function (resp) {
				core_overlay();
				console.error(error_ajax);
			});
        }
    }

    $(document).ready(function() {
        rebuild_vendors();
    });

    function core_overlay()
    {
        var overlays = $('body').find('.core-overlay');
        if(overlays.length == 0){
            $('body').append('<div class="core-overlay"><div class="core-loader"></div></div>')
        } else {
            overlays.remove();
        }
    }

    function error(text, title = null, _callback = null){
		var title_default = '<?=get_string('alert_error_title', 'local_core_facades')?>';
		if(!title || title === ''){
			title = title_default;
		}
		if(!text || text === ''){
			console.error("Nie można było wyświetlić alertu. Nieprawidłowa wartość treści. [" + text + "]");
		} else {

			Swal.fire({
				title: title,
				icon: "error",
				html: text,
				buttons: {
					confirm: {
						text: "Ok",
						value: true,
						visible: true,
						closeModal: true
					}
				},
				dangerMode: false,
			}).then((operation) => {
				var result = false;
				if (operation) {
					result = true;
				}

				if(_callback){
					_callback(result);
				}
			});
		}
	}

    function success(text, title = null, _callback = null){
		var title_default = '<?=get_string('alert_success_title', 'local_core_facades')?>';
		if(!title || title === ''){
			title = title_default;
		}
		if(!text || text === ''){
			console.error("Nie można było wyświetlić alertu. Nieprawidłowa wartość treści. [" + text + "]");
		} else {

			Swal.fire({
				title: title,
				icon: "success",
				html: text,
				buttons: {
					confirm: {
						text: "Ok",
						value: true,
						visible: true,
						closeModal: true
					}
				},
				dangerMode: false,
			}).then((operation) => {
				var result = false;
				if (operation) {
					result = true;
				}

				if(_callback){
					_callback(result);
				}
			});
		}
	}

    function alert_confirm(text, title = null, _callback = null){
        var title_default = '<?=get_string('alert_confirm_title', 'local_core_facades')?>';
        var cancel = '<?=get_string('cancel', 'local_core_facades')?>';
        var confirm = '<?=get_string('confirm', 'local_core_facades')?>';
		if(!title || title === ''){
			title = title_default;
		}
		if(!text || text === ''){
			console.error("Nie można było wyświetlić alertu. Nieprawidłowa wartość treści. [" + text + "]");
		} else {

			Swal.fire({
				title: title,
				html: text,
                icon: "question",
                showDenyButton: true,
                denyButtonText: cancel,
                confirmButtonText: confirm,
                timer: swal_timer
			}).then((operation) => {
				var result = false;
				if (operation.isConfirmed) {
					result = true;
				}

				if(_callback){
					_callback(result);
				}
			});
		}
	}

</script>