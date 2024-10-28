<?php

use \AcyChecker\Classes\TestClass;
use AcyChecker\Services\DateService;
use AcyChecker\Services\StatusService;
use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<div class="cell grid-x acyc__test__listing__listing">
	<div class="cell grid-x acyc__test__listing__listing__header">
		<div class="cell small-1 medium-shrink">
			<input id="checkbox_all" type="checkbox" name="checkbox_all">
		</div>
		<div class="cell small-3 acyc__listing__header__title">
            <?php echo __('Email', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center">
            <?php echo __('User status', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-large">
            <?php echo __('Date', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-large">
            <?php echo __('Trustworthiness', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-medium">
            <?php echo __('Domain exists', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-medium">
            <?php echo __('Disposable', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-medium">
            <?php echo __('Accept all', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-medium">
            <?php echo __('Role email', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center show-for-medium">
            <?php echo __('Free', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center">
            <?php echo __('Current step', 'acychecker'); ?>
		</div>
		<div class="cell auto acyc__listing__header__title text-center">
            <?php echo __('Actions', 'acychecker'); ?>
		</div>
	</div>
	<div class="cell grid-x acyc__test__listing__listing__body">
        <?php
        $rowId = 0;
        foreach ($this->data['elements'] as $test) {
            $rowId++;
            ?>
			<div class="cell grid-x acyc__listing__body__row">
				<div class="cell small-1 medium-shrink">
					<input id="checkbox_<?php echo Security::escape($rowId); ?>"
						   type="checkbox"
						   name="elements_checked[]"
						   value="<?php echo Security::escape($test->email); ?>"
						   data-acyc-finished="<?php echo Security::escape(intval($test->current_step) === TestClass::STEP['finished'] ? 'true' : 'false'); ?>">
				</div>
				<div class="cell grid-x small-3 acyc__listing__body__cell acyc__listing__tests__email">
                    <?php
                    echo '<div class="cell">'.$test->email.'</div>';

                    if (!empty($test->siteUserLink)) {
                        echo TooltipService::tooltip(
                            '<img src="'.ACYC_IMAGES.'icons/logo_'.ACYC_CMS.'.svg">',
                            sprintf(__('%s account', 'acychecker'), ACYC_CMS_TITLE),
                            'cell shrink',
                            '',
                            $test->siteUserLink
                        );
                    }

                    if (!empty($test->acyUserLink)) {
                        echo TooltipService::tooltip(
                            '<img src="'.ACYC_IMAGES.'icons/logo_acym.svg">',
                            __('AcyMailing subscriber', 'acychecker'),
                            'cell shrink',
                            '',
                            $test->acyUserLink
                        );
                    }

                    if (!empty($test->acy5UserLink)) {
                        echo TooltipService::tooltip(
                            '<img src="'.ACYC_IMAGES.'icons/logo_acymailing.png">',
                            __('AcyMailing 5 subscriber', 'acychecker'),
                            'cell shrink',
                            '',
                            $test->acy5UserLink
                        );
                    }
                    ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center">
                    <?php
                    if (empty($this->data['test_result_texts'][$test->test_result])) {
                        echo '-';
                    } else {
                        if (empty($test->block_reason)) {
                            echo TooltipService::tooltip('<i class="acycicon-check acyc_green"></i>', __('This email address was valid based on your configuration', 'acychecker'));
                        } else {
                            if ($test->block_reason === 'manual') {
                                $tooltipText = __('This email address has been manually blocked / removed from this listing', 'acychecker');
                            } else {
                                $tooltipText = sprintf(__('This email address has been blocked / removed based on your configuration: %s', 'acychecker'), $this->data['block_reasons'][$test->block_reason]);
                            }
                            echo TooltipService::tooltip(
                                '<i class="acycicon-times acyc_red"></i>',
                                $tooltipText,
                                '',
                                $this->data['block_reasons'][$test->block_reason]
                            );
                        }
                    }
                    ?>
				</div>
				<div class="cell auto acyc__listing__body__cell show-for-large">
                    <?php echo DateService::date($test->date, __('l, j F Y H:i', 'acychecker')); ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center show-for-large">
                    <?php echo empty($this->data['test_result_texts'][$test->test_result]) ? '-' : $this->data['test_result_texts'][$test->test_result]; ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center show-for-medium">
                    <?php echo empty($this->data['test_result_texts'][$test->test_result]) ? '-' : StatusService::yesNo($test->domain_exists, StatusService::RED_FOR_NO); ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center show-for-medium">
                    <?php echo empty($this->data['test_result_texts'][$test->test_result]) ? '-' : StatusService::yesNo($test->disposable); ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center show-for-medium">
                    <?php echo empty($this->data['test_result_texts'][$test->test_result]) ? '-' : StatusService::yesNo($test->accept_all); ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center show-for-medium">
                    <?php echo empty($this->data['test_result_texts'][$test->test_result]) ? '-' : StatusService::yesNo($test->role_email); ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center show-for-medium">
                    <?php echo empty($this->data['test_result_texts'][$test->test_result]) ? '-' : StatusService::yesNo($test->free, StatusService::NO_COLOR); ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center">
                    <?php echo $this->data['statuses'][$test->current_step]; ?>
				</div>
				<div class="cell auto acyc__listing__body__cell text-center">
                    <?php
                    if ($test->current_step != TestClass::STEP['finished']) {
                        echo '-';
                    } elseif ($test->removed) {
                        echo TooltipService::tooltip(
                            '<i class="acycicon-user-times"></i>',
                            __('User removed', 'acychecker')
                        );
                    } else {
                        echo TooltipService::tooltip(
                            '<i class="fastAction acycicon-check-circle"
							   data-acyc-action="unblockUsers"
							   data-acyc-elementid="'.Security::escape($rowId).'"></i>',
                            __('Unblock user', 'acychecker')
                        );
                        echo TooltipService::tooltip(
                            '<i class="fastAction acycicon-ban"
							   data-acyc-action="blockUsers"
							   data-acyc-elementid="'.Security::escape($rowId).'"></i>',
                            __('Block user', 'acychecker')
                        );
                        echo TooltipService::tooltip(
                            '<i class="fastAction acycicon-trash-o"
							   data-acyc-action="deleteUsers"
							   data-acyc-elementid="'.Security::escape($rowId).'"></i>',
                            __('Delete user', 'acychecker')
                        );
                    }
                    ?>
				</div>
			</div>
        <?php } ?>
	</div>
	<div class="cell grid-x margin-top-2">
        <?php echo $this->data['pagination']->display(); ?>
	</div>
</div>
