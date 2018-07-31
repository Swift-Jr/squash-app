<div class="row">
	<div class="col-sm-6">
		<h3>Recent Match's <?=anchor('matches/', 'More...')?></h3>
		<?foreach($MatchGroup as $Details):?>
			<? list($Game, $Matches) = $Details; ?>
			<h4><?=$Game->title?></h4><? $i = 1; ?>
			<table class="table">
			<?foreach($Matches as $Match):?>
				<tr>
					<td><?=$Match->date?></td>
					<td class="right"><?=anchor('/players/stats/'.$Match->p1->id(),$Match->p1->name)?></td>
					<td class="center"><?=$Match->p1_score?>-<?=$Match->p2_score?></td>
					<td><?=anchor('/players/stats/'.$Match->p2->id(),$Match->p2->name)?></td>
				</tr>
			<?endforeach;?>
			</table>
		<?endforeach;?>
	</div>
	<div class="col-sm-6">
		<h3>Super League</h3><? $i = 1; ?>
		<table class="table">
			<thead>
				<tr>
					<th>Pos.</th>
					<th>Name</th>
					<th>Played</th>
					<th>Won</th>
					<th>Avg Pt+</th>
					<th>Avg Pt-</th>
					<th>Avg +/-</th>
					<th>Points</th>
				</tr>
			</thead>
			<tbody>
			<?foreach($SuperLeague as $Line):?>
				<tr>
					<td><?=$i ?>.</td>
					<td><?=anchor('/players/stats/'.$Line->player->id(),$Line->player->name)?></td>
					<td><?=$Line->games_played ?></td>
					<td><?=$Line->won ?></td>
					<td><?=$Line->for ?></td>
					<td><?=$Line->against ?></td>
					<td><?=$Line->difference ?></td>
					<td><?=$Line->points ?></td>
				</tr><? $i++ ?>
			<?endforeach;?>
			</tbody>
		</table>
		<h3><?=date('F')?>'s League <?=anchor('matches/', 'More...')?></h3>
		<?foreach($Tables as $Details):?>
			<? list($Game, $Table) = $Details; ?>
			<h4><?=$Game->title?></h4><? $i = 1; ?>
			<table class="table">
				<thead>
					<tr>
						<th>Pos.</th>
						<th>Name</th>
						<th>Played</th>
						<th>Pt+</th>
						<th>Pt-</th>
						<th>+/-</th>
						<th>Points</th>
					</tr>
				</thead>
				<tbody>
				<?foreach($Table as $Line):?>
					<tr>
						<td><?=$i ?>.</td>
						<td><?=anchor('/players/stats/'.$Line->player->id(),$Line->player->name)?></td>
						<td><?=$Line->games_played ?></td>
						<td><?=$Line->for ?></td>
						<td><?=$Line->against ?></td>
						<td><?=$Line->difference ?></td>
						<td><?=$Line->points ?></td>
					</tr><? $i++ ?>
				<?endforeach;?>
				</tbody>
			</table>
		<?endforeach;?>
	</div>
</div>