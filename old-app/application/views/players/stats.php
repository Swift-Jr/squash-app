<div class="row">
	<div class="col-xs-12">
		<h3><?=$Player->name?></h3>
		<h4>Games <i><?=number_format($WinRatio*100,1)?>%</i></h4>
	</div>
	<div class="col-xs-12">
		<p><b>Played: </b><?=$Games?></p>
	</div>
	<div class="col-xs-6">
		<p><b>Wins: </b><?=$Wins?></p>
	</div>
	<div class="col-xs-6">
		<p><b>Losses: </b><?=$Losses?></p>
	</div>
	<div class="col-xs-12">
		<h4>Points <i><?=number_format($ScoreRatio*100, 1)?>%</i></h4>
	</div>
	<div class="col-xs-6">
		<p><b>Points Scored: </b><?=$For?></p>
	</div>
	<div class="col-xs-6">
		<p><b>Poinst Lost: </b><?=$Against?></p>
	</div>
</div>