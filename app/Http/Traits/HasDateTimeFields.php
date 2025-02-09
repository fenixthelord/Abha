<?php

namespace App\Http\Traits;
trait HasDateTimeFields
{
    protected $datetimeFields = [
        'created_at',
        'updated_at',
        'start_date',
        'end_date',
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
