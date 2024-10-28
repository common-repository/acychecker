<?php

use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<div class="cell grid-x acyc_content margin-top-2" id="acyc__dashboard__chart__container">
    <?php if (!empty($this->data['emptyStats'])) { ?>
		<h1 class="cell text-center acyc__dashboard__chart__title-empty"><?php echo __('You don\'t have any test, but here is an example of what the statistics look like', 'acychecker') ?></h1>
    <?php } ?>
	<div class="cell grid-x align-center margin-top-2">
		<h3 class="cell text-center acyc__dashboard__chart__title"><?php echo __('Users blocked this month', 'acychecker'); ?></h3>
        <?php if (!empty($this->data['emptyStatsBlocked']) && empty($this->data['emptyStats'])) { ?>
			<p class="cell text-center"><?php echo __('You don\'t have any blocked users, here are fake statistics', 'acychecker'); ?></p>
        <?php } ?>
		<div class="cell text-center"
			 id="acyc__dashboard__chart__blocked"
			 data-acyc-options="<?php echo Security::escape($this->data['block_reason']) ?>"></div>
	</div>
	<div class="cell grid-x align-center margin-top-2">
		<h3 class="cell text-center acyc__dashboard__chart__title"><?php echo __('Repartition of the all time tested emails', 'acychecker'); ?></h3>
        <?php foreach ($this->data['donutData'] as $donutData) { ?>
			<div class="cell medium-2"
				 id="acyc__dashboard__chart__<?php echo $donutData['nameKey']; ?>"
				 data-acyc-<?php echo $donutData['nameKey']; ?>="<?php echo $donutData['value']; ?>"></div>
        <?php } ?>
	</div>
	<div class="cell grid-x align-center margin-top-2">
		<h3 class="cell text-center acyc__dashboard__chart__title"><?php echo __('Here is the number of requests this month', 'acychecker') ?></h3>
		<div class="cell grid-x margin-top-1"
			 id="acyc__dashboard__chart__line"
			 data-acyc-options="<?php echo Security::escape(json_encode($this->data['month_calls'])); ?>"></div>
	</div>
</div>
