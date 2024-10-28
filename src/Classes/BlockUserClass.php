<?php

namespace AcyChecker\Classes;

use AcyChecker\Libraries\AcycClass;
use AcyChecker\Services\DebugService;
use AcyCheckerCmsServices\Database;

class BlockUserClass extends AcycClass
{
    const BLOCK_ACTION_REGISTRATION = 'Registration';
    const BLOCK_ACTION_BATCH = 'Batch';
    const BLOCK_ACTION_MANUAL = 'Manual';

    public $block_action;
    private $currentDate;

    public function __construct()
    {
        parent::__construct();

        $this->table = 'block_history';
        $this->pkey = 'email';

        $dateTimeNow = new \DateTime('NOW');
        $dateTimeNow->setTimezone(new \DateTimeZone('UTC'));
        $this->currentDate = $dateTimeNow->format('Y-m-d H:i:s');
    }

    public function save($element)
    {
        $element->block_date = $this->currentDate;
        $element->block_action = $this->block_action;

        return parent::save($element);
    }

    public function recordBlockAction($email, $reason)
    {
        $blockedEmail = new \stdClass();
        $blockedEmail->email = $email;
        $blockedEmail->block_reason = $reason;

        $this->save($blockedEmail);
    }
}
