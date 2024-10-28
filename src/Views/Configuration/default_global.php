<?php

use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<h2 class="cell acyc__title margin-top-2"><?php echo __('Global Configuration', 'acychecker'); ?></h2>
<div class="cell grid-x acyc_vcenter margin-y">
	<label class="cell medium-3 large-2" for="acyc__configuration__license__input"><?php echo Security::escape(__('License key', 'acychecker')); ?></label>
	<div class="cell medium-9 large-10">
		<input type="text"
			   id="acyc__configuration__license__input"
			   class="acyc__configuration__text__input"
			   name="config[license_key]"
			   value="<?php echo Security::escape($this->data['licenseKey']); ?>">
	</div>

	<div class="cell small-3 large-2">
		<a target="_blank"
		   href="<?php echo ACYC_ACYCHECKER_WEBSITE.'my-account/license?utm_source=acychecker_plugin&utm_campaign=get_my_key&utm_medium=button_get_my_key_dashboard'; ?>">
            <?php echo __('Get my key', 'acychecker'); ?>
		</a>
	</div>
	<div class="cell small-9 large-10 grid-x">
		<div id="acyc__configuration__button_container" class="cell grid-x align-right">
            <?php if (empty($this->data['licenseKey'])) { ?>
				<button class="cell shrink button acyc_button_submit" data-task="attachLicenseKey">
                    <?php echo __('Attach my license', 'acychecker'); ?>
				</button>
            <?php } else { ?>
				<button class="cell shrink button acyc_button_submit" data-task="detachLicenseKey">
                    <?php echo __('Detach my license', 'acychecker'); ?>
				</button>
            <?php } ?>
		</div>
	</div>

	<label class="cell medium-3 large-2 acyc_vcenter" for="acyc__configuration__blacklist__input">
        <?php
        echo TooltipService::tooltip(
            __('Blacklist addresses ending with', 'acychecker'),
            __('With this option you can blacklist email addresses with a specific domain/extension. Separate values with a coma. You can also enter a regular expression.', 'acychecker'),
            'cell medium-shrink'
        );
        ?>
	</label>
	<div class="cell medium-9 large-10">
		<input type="text"
			   id="acyc__configuration__blacklist__input"
			   class="acyc__configuration__text__input"
			   name="config[blacklist]"
			   value="<?php echo Security::escape($this->data['blacklist']); ?>"
			   placeholder="@excluded.com,.co.za,@[regex]{3}.com$...">
	</div>

	<label class="cell medium-3 large-2 acyc_vcenter" for="acyc__configuration__whitelist__input">
        <?php
        echo TooltipService::tooltip(
            __('Whitelist addresses ending with', 'acychecker'),
            __('With this option you can whitelist email addresses with a specific domain/extension. Separate values with a coma. You can also enter a regular expression.', 'acychecker'),
            'cell medium-shrink'
        );
        ?>
	</label>
	<div class="cell medium-9 large-10">
		<input type="text"
			   id="acyc__configuration__whitelist__input"
			   class="acyc__configuration__text__input"
			   name="config[whitelist]"
			   value="<?php echo Security::escape($this->data['whitelist']); ?>"
			   placeholder="@gmail.com,.co.uk,@[regex]{5}.com$...">
	</div>
</div>
