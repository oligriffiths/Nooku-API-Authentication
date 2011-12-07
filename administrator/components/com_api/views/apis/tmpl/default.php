<?php 





?>
<form action="<?= @route() ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<?= @helper('grid.checkall'); ?>
				</th>
				<th>
					<?= @text('Name'); ?>
				</th>
				<th>
					<?= @text('Key'); ?>
				</th>
				<th>
					<?= @text('Secret'); ?>
				</th>
				<th style="width: 50px">
					<?= @text('Enabled'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($apis AS $api){
				?>
				<tr>
					<td><?= @helper('grid.checkbox', array('row'=>$api)) ?></td>
					<td><?= @helper('html.link', array('url'=>@route('view=api&id='.$api->id), 'text' => $api->name)) ?></td>
					<td><?= $api->key ?></td>
					<td><?= $api->secret ?></td>
					<td style="text-align: center"><?= @helper('grid.enable', array('row' => $api)) ?></td>
				</tr>				
				<?php 
			} ?>
		</tbody>
		<tfoot>
		
		</tfoot>
	</table>
</form>