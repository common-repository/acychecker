<?php

use AcyChecker\Services\FormService;
use AcyChecker\Services\TooltipService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

?>
<h2 class="cell acyc__title"><?php echo __('Who?', 'acychecker'); ?></h2>
<div class="cell grid-x">
	<p class="cell medium-3"><?php echo __('Which user tables would you like to clean?', 'acychecker'); ?></p>
    <?php foreach ($this->data['tables_select'] as $tableInfo) { ?>
		<div class="cell shrink margin-right-1">
			<label>
				<input type="checkbox" <?php echo in_array($tableInfo['value'], $this->data['current_config']['tables_selected']) ? 'checked' : ''; ?>
					   value="<?php echo $tableInfo['value']; ?>"
					   name="acyc_config[tables_selected][<?php echo Security::escape($tableInfo['value']); ?>]">
				<span><?php echo $tableInfo['text']; ?></span>
			</label>
		</div>
    <?php } ?>
</div>
<?php foreach ($this->data['tables_filters'] as $nameKey => $tableFilter) { ?>
	<div class="cell grid-x" data-acyc-table="<?php echo Security::escape($nameKey); ?>">
		<label for="<?php echo Security::escape('table_filter_'.$nameKey); ?>" class="cell medium-3">
            <?php
            echo sprintf(__('%s additional filters', 'acychecker'),
                $tableFilter['text']
            );
            echo TooltipService::info(__('If you don\'t select any filter all users will be checked', 'acychecker'));
            ?>
		</label>
		<div class="cell large-3">
            <?php
            $selected = [];
            if (!empty($this->data['current_config']['table_filter_'.$nameKey])) {
                $selected = $this->data['current_config']['table_filter_'.$nameKey];
            }
            echo FormService::selectMultiple(
                $tableFilter['values'],
                'acyc_config[table_filter_'.$nameKey.']',
                $selected,
                [
                    'id' => 'table_filter_'.$nameKey,
                    'class' => 'acyc__select',
                ]
            );
            ?>
		</div>
	</div>
<?php } ?>
