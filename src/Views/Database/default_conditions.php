<?php

use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<h2 class="cell acyc__title margin-top-2 hide_on_do_nothing"><?php echo __('Conditions', 'acychecker'); ?></h2>
<div class="cell grid-x align-center hide_on_do_nothing">
	<p class="cell"><?php echo __('Block / Delete the user account if its address:', 'acychecker'); ?></p>
	<div class="cell medium-10 grid-x">
        <?php foreach ($this->data['condition_select'] as $conditionSelect) { ?>
			<label class="cell medium-6">
				<input type="checkbox" <?php echo in_array($conditionSelect['value'], $this->data['current_config']['conditions_selected']) ? 'checked' : ''; ?>
					   name="acyc_config[conditions_selected][<?php echo Security::escape($conditionSelect['value']); ?>]"
					   value="<?php echo Security::escape($conditionSelect['value']); ?>">
				<span><?php echo $conditionSelect['text'].TooltipService::info($conditionSelect['description']); ?></span>
			</label>
        <?php } ?>
	</div>
</div>
