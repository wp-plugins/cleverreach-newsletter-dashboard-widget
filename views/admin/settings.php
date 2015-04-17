<div class="haet-crd-widget-settings">
	<div class="haet-crd-status-bar"></div>
	<h2><?php _e('CleverReach dashboard settings','haet_cleverreach_dashboard'); ?></h2>
	<?php if($api_key && !$test_result['success']): ?>
		<p class="error"><?php  echo $test_result['message']; ?></p>
	<?php endif; ?>
	<label for="haet_cleverreach_dashboard_api_key"><?php _e('API key','haet_cleverreach_dashboard'); ?></label></th>
	<input type="text" class="" id="haet_cleverreach_dashboard_api_key" value="<?php echo $api_key; ?>">
	<div class="submit">
		<input type="submit" id="haet_cleverreach_dashboard_save" class="button-primary" value="<?php _e('Save', 'haet_cleverreach_dashboard') ?>" />
	</div>
	<p class="description"><?php _e('You can find the API key in your cleverreach account settings','haet_cleverreach_dashboard') ?></p>
</div>
		



