<?php

namespace App\Services\Excel;

use App\Models\Audit;

class AuditTransformer
{
    public function transform(Audit $audit): array
    {
        return [
            'User Name'       => $audit->user_full_name,
            'Event'           => $audit->event,
            'Auditable Type'  => $audit->auditable_type,
            'IP Address'      => $audit->ip_address,
            'User Agent'      => $audit->user_agent,
            'Tags'            => $audit->tags,
            'Exported By'     => auth()->user()->name ?? 'Unknown',
            'Created At'      => $audit->created_at->toDateTimeString(),
            'Created At hijri'=> $audit->hijri['created_at_hijri'],
        ];
    }

}
