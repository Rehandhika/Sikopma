<?php

namespace App\Exceptions;

use Exception;

class GeofenceException extends BusinessException
{
    public function __construct(string $message = 'Location is outside allowed area')
    {
        parent::__construct($message, 'GEOFENCE_VIOLATION', 403);
    }
}
