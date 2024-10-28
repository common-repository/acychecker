<?php

use AcyChecker\Services\FormService;
use AcyChecker\Services\StatusService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<div class="cell grid-x grid-margin-x">
	<input type="text"
		   id="acyc__test__listing__actions__search-input"
		   placeholder="<?php echo __('Search', 'acychecker'); ?>"
		   class="cell small-8 large-4"
		   name="search"
		   value="<?php echo Security::escape($this->data['search']); ?>">
	<button class="cell small-4 medium-shrink button"><?php echo __('Search', 'acychecker'); ?></button>
</div>
<div class="cell grid-x margin-top-1 margin-bottom-0 margin-y">
	<div class="cell medium-shrink grid-x acyc__listing__actions">
        <?php
        $actions = [
            'deleteResults' => __('Delete results', 'acychecker'),
            'blockUsers' => __('Block users', 'acychecker'),
            'unblockUsers' => __('Unblock users', 'acychecker'),
            'deleteUsers' => __('Delete users', 'acychecker'),
        ];
        echo FormService::listingActions($actions);
        ?>
	</div>
	<div class="cell medium-auto acyc_vcenter">
        <?php echo StatusService::initStatusListing($this->data['status'], empty($this->data['current_status']) ? 'all' : $this->data['current_status']); ?>
	</div>
	<div class="cell medium-auto acyc_listing_sort-by">
        <?php echo FormService::sortBy(
            [
                'email' => __('Email', 'acychecker'),
                'block_reason' => __('User status', 'acychecker'),
                'date' => __('Date', 'acychecker'),
                'current_step' => __('Current step', 'acychecker'),
            ],
            'tests',
            $this->data['ordering'],
            'asc'
        ); ?>
	</div>
</div>
