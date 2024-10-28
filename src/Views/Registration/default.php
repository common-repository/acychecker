<?php

use AcyChecker\Services\ViewService;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;

?>
<div id="acyc_configuration">
	<form action="" method="post" class="cell grid-x acyc_content" id="acyc_form">
        <?php include ViewService::getView('Registration', 'default_who'); ?>
        <?php include ViewService::getView('Registration', 'default_conditions'); ?>
        <?php include ViewService::getView('Registration', 'default_buttons'); ?>
        <?php Form::formOptions(true, 'save', null, 'registration'); ?>
	</form>
</div>
