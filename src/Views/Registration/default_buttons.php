<?php

use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;

?>

<div class="cell grid-x grid-margin-x align-center margin-top-1">
    <?php
    if (!empty($this->data['current_config']['registration_integrations'])) {
        echo TooltipService::tooltip(
            '<button class="cell shrink button button-secondary acyc_button_submit" data-task="stop" type="button">'.__('Disable', 'acychecker').'</button>',
            __('Disable user check during registration', 'acychecker')
        );
    }
    ?>
	<button class="cell shrink button acyc_button_submit"
			data-task="save"
			data-condition="tablesSelected">
        <?php echo __('Save', 'acychecker'); ?>
	</button>
</div>
