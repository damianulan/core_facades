<div class="modal fade core-modal" id="<?=uuid()?>" tabindex="-1" aria-labelledby="core-modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="core-modalLabel"><?=isset($title)?$title:''?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Zamknij">
                    <span aria-hidden="true" class="modal-close"><i class="bi bi-x-lg"></i></span>
                </button>
            </div>
            <?php include_once($resource_file_path); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        rebuild_vendors();
    });
</script>