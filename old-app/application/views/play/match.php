<div class="row">
	<div class="col-xs-12">
		<?=form_open('play/match')?>
		<p class="game">Game: <?=$Game->title?> <?=anchor('play/game', 'Change')?></p>
		<?=validation_error()?>
		<?=anchor('play/players/reset', 'Change Players')?>
		<div class="row">
			<div class="col-xs-5">
				<div class="form-group form-item">
					<label><?=$Player1->name?></label>
					<div class="input-padding">
						<button class="btn btn-default plusminus" data-target="player_1_score" data-operation="m">-</button><input type="text" name="player_1_score" class="form-control inlinecontrol" value="0"><button class="btn btn-default plusminus" data-target="player_1_score" data-operation="p">+</button>
					</div>
				</div>
			</div>
			<div class="col-xs-2">
				<label>vs.</label>
			</div>
			<div class="col-xs-5">
				<div class="form-group form-item">
					<label><?=$Player2->name?></label>
					<div class="input-padding">
						<button class="btn btn-default plusminus" data-target="player_2_score" data-operation="m">-</button><input type="text" name="player_2_score" class="form-control inlinecontrol" value="0"><button class="btn btn-default plusminus" data-target="player_2_score" data-operation="p">+</button>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<a href="<?=site_url('play/reset')?>" class="btn btn-default">Cancel</a><input class="btn btn-primary" type="submit" name="submit" value="Save Match" /></p>
			</div>
		</div>
		</form>
	</div>
</div>