<?php

namespace AcyChecker\Classes;

use AcyChecker\Libraries\AcycClass;

class DeleteUserClass extends AcycClass
{
    const DELETE_ACTION_BATCH = 'Batch';
    const DELETE_ACTION_MANUAL = 'Manual';

    public $delete_action;
    private $currentDate;

    public function __construct()
    {
        parent::__construct();

        $this->table = 'delete_history';
        $this->pkey = 'email';

        $dateTimeNow = new \DateTime('NOW');
        $dateTimeNow->setTimezone(new \DateTimeZone('UTC'));
        $this->currentDate = $dateTimeNow->format('Y-m-d H:i:s');
    }

    public function save($element)
    {
        $element->delete_date = $this->currentDate;
        $element->delete_action = $this->delete_action;

        return parent::save($element);
    }

    public function recordDeleteAction($email, $reason)
    {
        $deletedEmail = new \stdClass();
        $deletedEmail->email = $email;
        $deletedEmail->delete_reason = $reason;

        $this->save($deletedEmail);
    }
}
