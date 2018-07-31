<header>
	<div class="container header">
		<div class="row">
			<div class="col-sm-6">
				<h1>Squash App</h1>
			</div>
			<div class="col-sm-6">
				<nav>
					<ul>
						<li><?=anchor('home', 'Home')?></li>
						<li><?=anchor('play/reset', 'New Game')?></li>
						<li><?=anchor('games', 'Games')?></li>
						<li><?=anchor('players', 'Players')?></li>
					</ul>
				</nav>
			</div>
		</div>
	</div>
</header>
<div class="container">
	<div class="row">
		<?
		if($this->html->error() != FALSE){
			?><div class="col-sm-12">
			<p class="text-danger"><?=$this->html->error()?></p>
		</div>
	<?
		}
		if($this->html->info() != FALSE){
	?>	<div class="col-sm-12">
			<p class="text-info"><?=$this->html->info()?></p>
		</div>
	<?
		}
	?>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<h2><?=$PageHeader?></h2>
		</div>
	</div>
