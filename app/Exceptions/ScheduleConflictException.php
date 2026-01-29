<?php

namespace App\Exceptions;

class ScheduleConflictException extends BusinessException
{
    public function __construct(string $message = 'Schedule conflict detected')
    {
        parent::__construct($message, 'SCHEDULE_CONFLICT', 409);
    }
}
