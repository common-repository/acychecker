<?php

use AcyChecker\Services\ModalService;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

?>
<h2 class="cell acyc__title margin-top-2"><?php echo __('Logs', 'acychecker'); ?></h2>
<div class="cell grid-x margin-bottom-1">
	<label class="margin-right-1 medium-3"><?php echo Security::escape(__('When checking email addresses in bulk', 'acychecker')); ?></label>
    <?php
    echo ModalService::modal(
        __('See the logs', 'acychecker'),
        '',
        null,
        '',
        [
            'class' => 'button',
            'data-ajax' => 'true',
            'data-iframe' => '&ctrl=configuration&task=seeLogs&type=batch',
        ]
    );
    echo '<a href="'.Url::completeLink('configuration&task=deleteLogs&type=batch').'" class="margin-left-1 button">'.__('Delete the logs', 'acychecker').'</a>';
    ?>
</div>
<div class="cell grid-x margin-bottom-1">
	<label class="margin-right-1 medium-3"><?php echo Security::escape(__('When receiving results from our server', 'acychecker')); ?></label>
    <?php
    echo ModalService::modal(
        __('See the logs', 'acychecker'),
        '',
        null,
        '',
        [
            'class' => 'button',
            'data-ajax' => 'true',
            'data-iframe' => '&ctrl=configuration&task=seeLogs&type=callback',
        ]
    );
    echo '<a href="'.Url::completeLink('configuration&task=deleteLogs&type=callback').'" class="margin-left-1 button">'.__('Delete the logs', 'acychecker').'</a>';
    ?>
</div>
<div class="cell grid-x">
	<label class="margin-right-1 medium-3"><?php echo Security::escape(__('When checking an email address during registration', 'acychecker')); ?></label>
    <?php
    echo ModalService::modal(
        __('See the logs', 'acychecker'),
        '',
        null,
        '',
        [
            'class' => 'button',
            'data-ajax' => 'true',
            'data-iframe' => '&ctrl=configuration&task=seeLogs&type=individual',
        ]
    );
    echo '<a href="'.Url::completeLink('configuration&task=deleteLogs&type=individual').'" class="margin-left-1 button">'.__('Delete the logs', 'acychecker').'</a>';
    ?>
</div>
