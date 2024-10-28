<?php

use AcyCheckerCmsServices\Language;

?>
<h2 class="cell acyc__title"><?php echo __('Who?', 'acychecker'); ?></h2>
<div class="cell grid-x">
	<p class="cell medium-3"><?php echo __('Registration type', 'acychecker'); ?></p>
    <?php
    foreach ($this->data['tables_select'] as $tableInfo) {
        $checked = in_array($tableInfo['value'], $this->data['current_config']['registration_integrations']) ? 'checked' : '';
        ?>
		<div class="cell shrink margin-right-1">
			<label>
				<input
						type="checkbox" <?php echo $checked; ?>
						name="acyc_config[registration_integrations][<?php echo $tableInfo['value']; ?>]"
						value="<?php echo $tableInfo['value']; ?>">
				<span><?php echo $tableInfo['text']; ?></span>
			</label>
		</div>
    <?php } ?>
</div>
