<?php

use AcyChecker\Services\ViewService;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

?>
<form class="cell grid-x acyc_content acyc_form" id="acyc_form" method="post" action="<?php echo Url::completeLink(Security::getVar('cmd', 'ctrl')); ?>">
    <?php
    include ViewService::getView('Tests', 'listing_buttons');
    include ViewService::getView('Tests', 'listing_filters');
    if (empty($this->data['elements'])) {
        echo '<h2 class="cell text-center">'.__('There is no test to display', 'acychecker').'</h2>';
    } else {
        include ViewService::getView('Tests', 'listing_results');
    }
    Form::formOptions(true, 'listing', null, 'tests');
    ?>
</form>
