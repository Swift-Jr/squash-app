<p><?=anchor('players/create', 'Add New')?></p>
<?php if(count($Players) <= 0): ?>
<p>No Games</p>
<?php endif; ?>
<ul>
<?foreach($Players as $Player):?>
	<li><?=$Player->name; ?><?
		//echo anchor('games/postback/'.$Game->id().'/edit', 'Edit');
		echo anchor('players/postback/'.$Player->id().'/delete', 'Delete');
	?></li>
<?endforeach;?>
</ul>