<p><?=anchor('games/create', 'Add New')?></p>
<?php if(count($Games) <= 0): ?>
<p>No Games</p>
<?php endif; ?>
<ul>
<?foreach($Games as $Game):?>
	<li><?=$Game->title; ?><?
		//echo anchor('games/postback/'.$Game->id().'/edit', 'Edit');
		echo anchor('games/postback/'.$Game->id().'/delete', 'Delete');
	?></li>
<?endforeach;?>
</ul>