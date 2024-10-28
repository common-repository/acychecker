<?php

use AcyChecker\Services\ViewService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Url;

?>
<div class="cell grid-x acyc_content">
	<div class="cell"><?php echo __('You currently have:', 'acychecker'); ?></div>
	<ul class="cell">
		<li>
            <?php echo sprintf(__('%1$s %2$s users (average %3$s new users per month during last year)', 'acychecker'),
                '<b>'.$this->data['cmsUsers'].'</b>',
                ACYC_CMS_TITLE,
                $this->data['cmsUsersEvolution']
            ); ?>
		</li>
        <?php if (!empty($this->data['acyUsers'])) { ?>
			<li>
                <?php echo sprintf(__('%1$s %2$s users (average %3$s new users per month during last year)', 'acychecker'),
                    '<b>'.$this->data['acyUsers'].'</b>',
                    'AcyMailing',
                    $this->data['acyUsersEvolution']
                ); ?>
			</li>
        <?php } ?>
        <?php if (!empty($this->data['acy5Users'])) { ?>
			<li>
                <?php echo sprintf(__('%1$s %2$s users (average %3$s new users per month during last year)', 'acychecker'),
                    '<b>'.$this->data['acy5Users'].'</b>',
                    'AcyMailing 5',
                    $this->data['acy5UsersEvolution']
                ); ?>
			</li>
        <?php } ?>
	</ul>
    <?php include ViewService::getView('Dashboard', 'default_free'); ?>
    <?php if (!empty($this->config->get('license_level')) && $this->config->get('license_level') !== 'AcyChecker-Starter') { ?>
		<div class="cell medium-6 text-center margin-y">
			<a class="button button-secondary" href="<?php echo Url::completeLink('database'); ?>">
                <?php echo __('Clean my database', 'acychecker'); ?>
			</a>
		</div>
		<div class="cell medium-6 text-center">
			<a class="button button-secondary" href="<?php echo Url::completeLink('registration'); ?>">
                <?php echo __('Block fake users registration', 'acychecker'); ?>
			</a>
		</div>
    <?php } ?>
</div>
