<?php
    if($filterBuilder){
        $filterBuilder->render();
    }
?>
<div class="datatable-container" id="<?=$uuid?>_container" data-uuid="<?=$uuid?>">
    <div class="pagination-container">
        <?php include('pagination.php')?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table core-table">
                <thead>
                    <tr>
                        <?php foreach($headers as $header): ?>
                            <th class="<?=$header->getClasses()?>" width="<?=$header->getWidth()?>"><?=$header->getText()?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if($count > 0 && !empty($records)): ?>
                        <?php 
                        foreach($records as $record): 
                            echo '<tr>';
                            $rows = $datatable->row($record);

                            foreach($rows as $row):
                            ?>
                                <td class="<?=$row->getClasses()?>"<?=$row->getAttributes()?>>
                                    <?php 
                                        $content = trim($row->getContent());
                                        if($content === '' || is_null($content)){
                                            $content = core_lang('no_data');
                                        }

                                        echo $content;
                                    ?>
                                </td>
                            <?php endforeach;
                            echo '</tr>';
                            endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="100%"><?=core_lang('no_data_table')?></td>
                        </tr>
                    <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination-container">
        <?php include('pagination.php')?>
    </div>
</div>
