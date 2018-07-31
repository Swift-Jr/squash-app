<div class="row">
	<div class="col-xs-12">
		<p class="game">Game: <?=$Game->title?> <?=anchor('play/game', 'Change')?></p>
		<?if(isset($Player1)):?>
		<p><?=$Player1?> vs. ???</p>
		<?endif;?>
		<h4>Select Player <?=$PlayerNum?></h4>
		<?php if(count($Players) <= 0): ?>
		<p>No Players</p>
		<?php endif; ?>
		<?foreach($Players as $Player):?>
			<a class="btn btn-default" href="<?=site_url('play/players/'.$Player->id()) ?>"><?=$Player->name; ?></a>
		<?endforeach;?>
	</div>
</div>
<div class="row mb">
	<div class="col-xs-12">
		<a href="<?=site_url('play/reset')?>" class="btn btn-default">Cancel</a>
	</div>
</div>
