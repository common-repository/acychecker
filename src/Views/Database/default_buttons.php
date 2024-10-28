<?php

use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;

?>

<div class="cell grid-x grid-margin-x align-center">
    <?php
    if ($this->data['allow_stop_periodic']) {
        echo TooltipService::tooltip(
            '<button class="cell shrink button button-secondary acyc_button_submit" data-task="stop" type="button">'.__('Stop periodic tests', 'acychecker').'</button>',
            sprintf(__('You configured AcyChecker to verify your user base %s, would you like to stop these periodic tests?', 'acychecker'), strtolower($this->data['execution_select'][$this->data['current_config']['execution_selected']]['text']))
        );
    }
    ?>
	<button class="cell shrink button acyc_button_submit"
			data-task="save"
			data-condition="tablesSelected"
			id="acyc__database__save__button">
        <?php echo __('Save', 'acychecker'); ?>
	</button>
</div>
