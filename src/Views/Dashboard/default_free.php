<?php

use AcyCheckerCmsServices\Language;

if (in_array($this->config->get('license_level', ''), ['', 'AcyChecker-Starter']) && $this->data['totalDisposableEmails'] > 0) {
    $acyText = '';
    if (!empty($this->data['acyUsers'])) {
        $acyText = sprintf(__('and %1$s %2$s users', 'acychecker'),
            '<b>'.$this->data['disposableAcyEmails'].'</b>',
            'acymailing'
        );
    }
    $linkGetLicense = ACYC_ACYCHECKER_WEBSITE.'pricing?utm_source=acychecker_plugin&utm_campaign=get_license&utm_medium=button_get_license';
    ?>
	<div class="cell">
        <?php echo sprintf(__('Based on a sample of your users, potentially more than %1$s %2$s users %3$s are using emails addresses that could be invalid.', 'acychecker'),
            '<b>'.$this->data['disposableCmsEmails'].'</b>',
            ACYC_CMS_TITLE,
            $acyText
        ); ?>
	</div>
	<div class="cell margin-bottom-1">
        <?php echo sprintf(__('According to this test we recommend using this plan: %s', 'acychecker'),
            $this->data['suggestedPlan']
        ); ?>
	</div>
	<div class="cell grid-x align-center margin-y">
		<a class="cell shrink button" href="<?php echo $linkGetLicense; ?>" target="_blank">
            <?php echo __('Get a license', 'acychecker'); ?>
		</a>
	</div>
    <?php
}
