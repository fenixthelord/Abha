<?php

namespace app\Services\Excel;

use App\Models\Audit;
use Carbon\Carbon;

class AuditTransformer
{
    public function transform(Audit $audit): array
    {


        return [

            'User Type'         => $audit->user_type,
            'User Full Name'    => $audit->user_full_name,
            'Event'             => $audit->event,
            'Auditable Type'    => $audit->auditable_type,
            'Auditable ID'      => $audit->auditable_id,
            'Old Values'        => $audit->old_values,
            'New Values'        => $audit->new_values,
            'URL'               => $audit->url,
            'IP Address'        => $audit->ip_address,
            'User Agent'        => $audit->user_agent,
            'Tags'              => $audit->tags,
            'Created At'        => Carbon::parse($audit->created_at)->format('Y-m-d H:i:s'),
            'Created At Hijri'  => $audit->hijri['created_at_hijri'] ?? '',
            'Exported By'       => auth()->user()->name ?? 'Unknown',
        ];
    }

}
