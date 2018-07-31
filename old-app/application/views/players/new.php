<?if(function_exists('validation_error')){echo validation_error();}?>
<?=form_open('players/create')?>
<?
	$Input = new ifx_FormItem();
	echo $Input->Name('player_name')->Label('Name');
?>
<a class="btn btn-default" href="<?=site_url('games/')?>">Cancel</a>
<input type="submit" name="submit" value="Create" class="btn btn-primary">
<?=form_close()?>