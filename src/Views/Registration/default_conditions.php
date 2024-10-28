<?php

use AcyCheckerCmsServices\Language;

?>
<h2 class="cell acyc__title margin-top-2"><?php echo __('Conditions', 'acychecker'); ?></h2>
<div class="cell grid-x align-center">
	<p class="cell"><?php echo __('Prevent the subscription if the email address:', 'acychecker'); ?></p>
	<div class="cell medium-10 grid-x">

        <?php
        foreach ($this->data['condition_select'] as $conditionSelect) {
            $checked = in_array($conditionSelect['value'], $this->data['current_config']['registration_conditions']) ? 'checked' : '';
            ?>
			<label class="cell medium-6">
				<input
						type="checkbox" <?php echo $checked; ?>
						name="acyc_config[registration_conditions][<?php echo $conditionSelect['value']; ?>]"
						value="<?php echo $conditionSelect['value']; ?>">
				<span><?php echo $conditionSelect['text']; ?></span>
			</label>
        <?php } ?>
	</div>
</div>
