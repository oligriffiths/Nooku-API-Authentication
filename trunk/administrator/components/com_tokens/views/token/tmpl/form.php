<? defined('KOOWA') or die('Restricted access'); ?>

<script src="media://lib_koowa/js/koowa.js" />
<style src="media://lib_koowa/css/koowa.css" />

<?= @helper('behavior.keepalive'); ?>
<?= @helper('behavior.validator') ?>

<form action="<?= @route('id='.$token->id) ?>" method="post" class="-koowa-form" id="edit-form" >
	<div class="grid_8" id="panels">
        <div class="panel">
            <h3>Settings</h3>
            <table class="paramlist admintable">
                <tbody>
                	<tr>
	                    <td class="paramlist_key">
	                        <label for="name"><?= @text('Name'); ?></label>
	                    </td>
	                    <td>
	                    	<input id="name" type="name" class="title minLength:5" size="40" name="name" value="<?= $token->name; ?>"  />
	                    </td>
	                </tr>
	                <tr>
	                    <td class="paramlist_key">
	                        <label for="enabled"><?= @text('Enabled'); ?></label>
	                    </td>
	                    <td>
	                    	<?= @helper('select.booleanlist', array('selected'=>$token->enabled, 'name'=>'enabled')) ?>
	                    </td>
	                </tr>
	                <tr>
	                    <td class="paramlist_key">
	                        <label><?= @text('Key'); ?></label>
	                    </td>
	                    <td>
	                    	<?= $token->id ? $token->key : @text('Save to generate key') ?>
	                    </td>
	                </tr>
	                <tr>
	                    <td class="paramlist_key">
	                        <label><?= @text('Secret'); ?></label>
	                    </td>
	                    <td>
	                    	<?= $token->id ? $token->secret : @text('Save to generate secret') ?>
	                    </td>
	                </tr>	
	                <?php if($token->id){ ?>
	                <tr>
	                    <td class="paramlist_key">
	                        <label for="generate_secret"><?= @text('Generate new Secret'); ?></label>
	                    </td>
	                    <td>
	                    	<input type="checkbox" id="generate_secret" name="generate_secret" value="1" onclick="if(this.checked) return confirm('<?= @text('Are you sure you wish to generate a new secret? You will need to re-issue this secret to any clients using this key') ?>')" />
	                    </td>
	                </tr>	  
	                <?php } ?>
	                <tr>
	                    <td class="paramlist_key">
	                        <label for="ip_whitelist"><?= @text('Whitelisted IP\'s'); ?></label>
	                    </td>
	                    <td>
	                    	<textarea id="ip_whitelist" name="ip_whitelist" rows="5" cols="50"><?= $token->ip_whitelist ?></textarea>
	                    </td>
	                </tr>
	                <tr>
	                    <td class="paramlist_key">
	                        <label for="ip_blacklist"><?= @text('Blacklisted IP\'s'); ?></label>
	                    </td>
	                    <td>
	                    	<textarea id="ip_blacklist" name="ip_blacklist" rows="5" cols="50"><?= $token->ip_blacklist ?></textarea>
	                    </td>
	                </tr>	
	                <tr>
	                    <td class="paramlist_key">
	                        <label for="max_requests"><?= @text('Max Requests / Hr.'); ?></label>
	                    </td>
	                    <td>
	                    	<input type="text" id="max_requests" name="max_requests" size="4" value="<?= $token->requests_max ?>" />
	                    </td>
	                </tr>	
	                <tr>
	                    <td class="paramlist_key">
	                        <label><?= @text('Requests in the last hour'); ?></label>
	                    </td>
	                    <td>
	                    	<?= date('Y-m-d H', strtotime($token->last_request)) == date('Y-m-d H') ? $token->requests_in_last_hour : 0 ?>
	                    </td>
	                </tr>	
	                <tr>
	                    <td class="paramlist_key">
	                        <label><?= @text('Total requests'); ?></label>
	                    </td>
	                    <td>
	                    	<?= $token->requests_total ?>
	                    </td>
	                </tr>                
           		</tbody>
            </table>
        </div>       
    </div>
</form>