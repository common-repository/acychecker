<?php

use AcyChecker\Services\ViewService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

?>
<div class="cell grid-x grid-margin-x acyc__test__listing__actions margin-y align-right margin-bottom-0">
	<button type="button"
			id="acyc__test__listing__actions-cancel"
			class="cell small-6 large-shrink button button-secondary"
			data-acyc-redirect="<?php echo Url::completeLink('tests&task=cancelPending'); ?>"
			data-acyc-confirmation="<?php echo Security::escape(__('Please confirm you would like to cancel pending tests', 'acychecker')); ?>">
        <?php echo __('Cancel pending tests', 'acychecker'); ?>
		<i class="acycicon-times-circle"></i>
	</button>
	<button type="button"
			id="acyc__test__listing__actions-delete"
			class="cell small-6 large-shrink button button-secondary"
			data-acyc-redirect="<?php echo Url::completeLink('tests&task=clearTested'); ?>"
			data-acyc-confirmation="<?php echo Security::escape(__('Please confirm you would like to delete all the previous test results', 'acychecker')); ?>">
        <?php echo __('Clear finished tests', 'acychecker'); ?>
		<i class="acycicon-trash-o"></i>
	</button>
	<button type="button"
			id="acyc__test__listing__actions-export"
			class="cell small-6 large-shrink button button-secondary"
			data-acyc-redirect="<?php echo Url::completeLink('tests&task=doexport&noheader=1'); ?>">
        <?php echo __('Export test results', 'acychecker'); ?>
		<i class="acycicon-download"></i>
	</button>
	<button type="button"
			id="acyc__test__listing__actions-exportBlockedUsers"
			class="cell small-6 large-shrink button button-secondary"
			data-acyc-redirect="<?php echo Url::completeLink('tests&task=doExportBlockedUsers&noheader=1'); ?>">
        <?php echo __('Export blocked users', 'acychecker'); ?>
		<i class="acycicon-download"></i>
	</button>
	<button type="button"
			id="acyc__test__listing__actions-exportDeletedUsers"
			class="cell small-6 large-shrink button button-secondary"
			data-acyc-redirect="<?php echo Url::completeLink('tests&task=doExportDeletedUsers&noheader=1'); ?>">
        <?php echo __('Export deleted users', 'acychecker'); ?>
		<i class="acycicon-download"></i>
	</button>
    <?php include ViewService::getView('Tests', 'listing_handle_modal'); ?>
</div>
