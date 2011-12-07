<? defined('KOOWA') or die('Restricted access'); ?>

<script src="media://lib_koowa/js/koowa.js" />
<style src="media://lib_koowa/css/koowa.css" />

<?= @helper('behavior.keepalive'); ?>
<?= @helper('behavior.validator') ?>

<form action="<?= @route('id='.$api->id) ?>" method="post" class="-koowa-form" id="edit-form" >
	<div class="grid_8" id="mainform">
		
		
		<div class="panel clearfix">
			<label for="name" class="mainlabel"><?= @text('Name'); ?></label>
			<input id="name" type="name" class="title minLength:5" name="name" value="<?= $api->name; ?>"  />
		</div>
		<div class="panel clearfix">
			<label for="secret" class="mainlabel"><?= @text('Enabled'); ?></label>
			<?= @helper('select.booleanlist', array('selected'=>$api->enabled, 'name'=>'enabled')) ?>
		</div>
		<div class="panel clearfix">
			<label for="secret" class="mainlabel"><?= @text('Key'); ?></label>
			<?= $api->id ? $api->key : @text('Save to generate key') ?>
		</div>
		<div class="panel clearfix">
			<label for="secret" class="mainlabel"><?= @text('Secret'); ?></label>
			<?= $api->id ? $api->secret : @text('Save to generate secret') ?>
		</div>
		<?php if($api->id){ ?>
		<div class="panel clearfix">
			<label for="generate_secret" class="mainlabel"><?= @text('Generate Secret'); ?></label>
			<input type="checkbox" id="generate_secret"" name="generate_secret" value="1" />
		</div>
		<?php } ?>
	</div>
</form>