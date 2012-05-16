<? defined('KOOWA') or die('Restricted access'); ?>
<script src="media://lib_koowa/js/koowa.js" />
<style src="media://lib_koowa/css/koowa.css" />

<form id="articles-form" action="" method="get" class="-koowa-grid">
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
				<th>
					<?= @text('Last Request'); ?>
				</th>
				<th>
					<?= @text('Requests in last hour'); ?>
				</th>
				<th>
					<?= @text('Total Requests'); ?>
				</th>
				<th style="width: 50px">
					<?= @text('Enabled'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($tokens AS $token){
				?>
				<tr>
					<td><?= @helper('grid.checkbox', array('row'=>$token)) ?></td>
					<td><?= @helper('html.link', array('url'=>@route('view=token&id='.$token->id), 'text' => $token->name)) ?></td>
					<td><?= $token->key ?></td>
					<td><?= $token->secret ?></td>
					<td><?= $token->last_request ?></td>
					<td><?= date('Y-m-d H', strtotime($token->last_request)) == date('Y-m-d H') ? $token->requests_in_last_hour : 0 ?></td>
					<td><?= $token->requests_total ?></td>
					<td style="text-align: center"><?= @helper('grid.enable', array('row' => $token)) ?></td>
				</tr>				
				<?php 
			} ?>
		</tbody>
		<tfoot>
			 <tr>
                <td colspan="8">
                    <?= @helper('paginator.pagination', array('total' => $total)) ?>
                </td>
            </tr>
		</tfoot>
	</table>
</form>