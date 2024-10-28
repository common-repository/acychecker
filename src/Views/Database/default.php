<?php

use AcyChecker\Services\ViewService;
use AcyCheckerCmsServices\Form;

?>
<div id="acyc_configuration">
	<form action="" method="post" class="cell grid-x acyc_content" id="acyc_form">
        <?php include ViewService::getView('Database', 'default_who'); ?>
        <?php include ViewService::getView('Database', 'default_action'); ?>
        <?php include ViewService::getView('Database', 'default_conditions'); ?>
        <?php include ViewService::getView('Database', 'default_when'); ?>
        <?php include ViewService::getView('Database', 'default_buttons'); ?>
        <?php Form::formOptions(true, 'save', null, 'database'); ?>
	</form>
</div>
