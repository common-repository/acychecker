<?php

use AcyChecker\Services\ViewService;

?>
<div id="acyc__dashboard" class="cell grid-x">
    <?php include ViewService::getView('Dashboard', 'default_current'); ?>
    <?php include ViewService::getView('Dashboard', 'default_charts'); ?>
</div>
