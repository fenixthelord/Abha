<?php

namespace App\Http\Traits;
trait HasDateTimeFields
{
    protected $datetimeFields = [
        'start_date',
        'end_date',
        'email_verified_at',
        'otp_expires_at',
        'refresh_token_expires_at',
        'schedule_at',
        'reserved_at',
        'available_at',
        'created_at',
        'failed_at',
    ];

    protected function initializeHasDateTimeFields()
    {
        foreach ($this->datetimeFields as $field) {
            $this->casts[$field] = 'datetime';
        }
    }

    protected function formatDateTime($date)
    {
        return $date->format('d-M-Y');
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->datetimeFields) && !is_null($value)) {
            return $this->formatDateTime($value);
        }

        return $value;
    }
}
