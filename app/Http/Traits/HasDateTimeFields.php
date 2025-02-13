<?php

namespace App\Http\Traits;
use Carbon\Carbon;

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
        'updated_at',
        'deleted_at',
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
    public function getHijriAttribute()
    {
        $attributes = [];
        foreach ($this->datetimeFields as $dateField) {
            if (!empty($this->$dateField)) {
                $date = Carbon::parse($this->$dateField);
                $attributes[$dateField . '_hijri'] =  \Alkoumi\LaravelHijriDate\Hijri::Date('l d F Y', $date);
            }
        }
        return $attributes;
    }
    public function setHijriAttribute($value)
    {
        $this->customField = $value;
    }

}
