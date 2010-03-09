
<div class="paging">
	<?php echo $paginator->prev(__('前', true), array(), null, array('class'=>'disabled'));?>
	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__('次', true), array(), null, array('class' => 'disabled'));?>
</div>