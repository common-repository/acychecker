<?php

use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<h2 class="cell acyc__title margin-top-2"><?php echo __('Action', 'acychecker'); ?></h2>
<div class="cell grid-x">
	<p class="cell medium-3"><?php echo __('What action would you like to take for the fake users?', 'acychecker'); ?></p>
    <?php foreach ($this->data['action_select'] as $actionSelect) { ?>
		<div class="cell shrink margin-right-1">
			<label>
				<input id="<?php echo Security::escape($actionSelect['value']); ?>"
					   type="radio" <?php echo $this->data['current_config']['action_selected'] === $actionSelect['value'] ? 'checked' : ''; ?>
					   name="acyc_config[action_selected]"
					   value="<?php echo Security::escape($actionSelect['value']); ?>">
				<span><?php echo $actionSelect['text']; ?></span>
			</label>
		</div>
    <?php } ?>
</div>
