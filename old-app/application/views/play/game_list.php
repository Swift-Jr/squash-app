<?php if(count($Games) <= 0): ?>
<p>No Games</p>
<?php endif; ?>
<ul>
<?foreach($Games as $Game):?>
	<li><a href="<?=site_url('play/game/'.$Game->id()) ?>"><?=$Game->title; ?></a></li>
<?endforeach;?>
</ul>