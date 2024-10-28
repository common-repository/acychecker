<?php

use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;

?>
<h2 class="cell acyc__title margin-top-2"><?php echo __('Maintenance', 'acychecker'); ?></h2>
<div class="cell grid-x">
    <?php
    echo TooltipService::tooltip(
        '<button type="button" class="cell medium-shrink button button-secondary" id="checkdb_button">'.__('Check database integrity', 'acychecker').'</button>',
        __('This button will check if there is any issue in the database like a missing table/column etc...<br />If you have any issue with your database it\'s the first thing to do', 'acychecker'),
        'cell medium-shrink'
    );
    ?>
	<div class="cell auto padding-left-1" id="checkdb_report"></div>
</div>
