<?php
$last_page = ceil($count/$pagination);

if(($current_page-floor($pages_to_show/2))<$current_page) {
    $first_pages_to_show = 1;
} else {
    $first_pages_to_show = $current_page-floor($pages_to_show/2);
}
if(($current_page+ceil($pages_to_show/2))>$last_page) {
    $last_pages_to_show = $last_page;
} else {
    $last_pages_to_show = $current_page+ceil($pages_to_show/2);
}

$dir = isset($order)&&$order=='ASC' ? 'DESC':'ASC';
?>

<?php if($count > $min_pagination): ?>
<div class="pagination-select">
    <select class="form-control pagination_select" id="<?=$uuid?>_pagination" data-uuid="<?=$uuid?>">
        <?php foreach($datatable->getPagination() as $value): ?>
            <option value="<?=$value?>"<?=$value==$pagination ? ' selected':''?>><?=$value?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<?php if($last_page > 1): ?>
<nav class="pagination ml-auto">
    <ul class="pagination">
        <?php

        $active = '';

		if($current_page>1) { ?>
			<li class='page-item arrow'>
                <a href='javascript:void(0);' 
                    onclick="datatableLoad('<?=$uuid?>',1,<?=$pagination?>)" class="page-link">
                    <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                </a>
            </li>
            <?php
			$page_x = $current_page - 1;
            ?>
			<li class='page-item'>
                <a href='javascript:void(0);' onclick="datatableLoad('<?=$uuid?>',<?=$page_x?>,<?=$pagination?>)" class="page-link">
                    <i class="fa fa-angle-left" aria-hidden="true"></i>
                </a>
            </li>

        <?php }

		if($current_page != 1 && $current_page - $pages_to_show/2  >  1 ) { ?>
			<li class='page-item'>
                <a href='javascript:void(0);' onclick="datatableLoad('<?=$uuid?>',1,<?=$pagination?>)" class="page-link">1</a>
            </li>
			<?php
            if($current_page - $pages_to_show/2  >  2) { ?>
				<li class="page-item"><div>&hellip;</div></li>
			<?php }
		}

		for($i=$first_pages_to_show; $i<=$last_pages_to_show; $i++)
		{
			if($i>$last_page)
				break;

			if($i<1)
				continue;

			$active = '';
			if($current_page==$i){
				$active = ' active ';
			}
            ?>

            <li class='page-item <?=$active?>'>
                <a href='javascript:void(0);'  class="page-link" onclick="datatableLoad('<?=$uuid?>',<?=$i?>,<?=$pagination?>)"><?=$i?></a>
            </li>
		<?php }

		if($current_page != $last_page && $current_page + $pages_to_show/2 < $last_page) {
			if($current_page + $pages_to_show/2 < $last_page-1) { ?>
				<li class="page-item"><div>&hellip;</div></li>
			<?php }
			?>
            <li class='page-item'>
                <a href='javascript:void(0);' class="page-link" onclick="datatableLoad('<?=$uuid?>',<?=$last_page?>,<?=$pagination?>)"><?=$last_page?></a>
            </li>
		<?php }

		$active = '';

		if($current_page<$last_page)
		{
			$page_plus = $current_page + 1;
            ?>
			<li class='page-item arrow'>
                <a href='javascript:void(0);' onclick="datatableLoad('<?=$uuid?>',<?=$page_plus?>,<?=$pagination?>)" class="page-link">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </li>

			<li class='page-item arrow'>
                <a href='javascript:void(0);' onclick="datatableLoad('<?=$uuid?>',<?=$last_page?>,<?=$pagination?>)" class="page-link">
                    <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                </a>
            </li>
		<?php } ?>

    </ul>
</nav>
<?php endif; ?>

