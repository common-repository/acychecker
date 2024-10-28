<?php

use AcyChecker\Services\ViewService;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;

?>
<div id="acyc_configuration" class="cell">
	<form action="" method="post" class="cell grid-x acyc_content" id="acyc_form">
        <?php include ViewService::getView('Configuration', 'default_global'); ?>
        <?php include ViewService::getView('Configuration', 'default_logs'); ?>
        <?php include ViewService::getView('Configuration', 'default_maintenance'); ?>

		<div class="cell grid-x align-center margin-top-2">
			<button class="cell shrink button"><?php echo __('Save', 'acychecker'); ?></button>
		</div>
        <?php Form::formOptions(true, 'save', null, 'configuration'); ?>
	</form>
</div>
