<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\FileExportReportTrait;

class ExportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FileExportReportTrait;

    protected $exportData;
    protected $filename;
    protected $userIds;


    public function __construct(array $exportData, string $filename = 'Report.xlsx', array $userIds = [])
    {
        $this->exportData = $exportData;
        $this->filename = $filename;
        $this->userIds = $userIds;
    }


    public function handle(): void
    {
        try {

            $excelFileUrl = $this->exportData($this->exportData, $this->filename);
            $userId = auth('sanctum')->id();
            $dateNow = date('Ymd');
            $channelName = "pusher_{$this->filename}_{$dateNow}_{$userId}";

            $params = [
                'sender_id'        => null,
                'sender_type'      => 'user',
                'sender_service'   => 'service_user',
                'title'            => 'Excel Export Completed',
                'body'             => 'Your exported Excel file is ready.',
                'user_ids'         => $this->userIds,
                'receiver_service' => 'service_user',
                'receiver_type'    => 'user',
                'group_id'         => null,
                'channel'          => $channelName,
                'image'            => null,
                'url'              => $excelFileUrl,
            ];


            $notificationResponse = app('App\Services\NotificationService')->postCall('/notifications/send-pusher', $params);

            Log::info('Notification API response:', ['response' => $notificationResponse]);
        } catch (\Exception $e) {
            Log::error('Export Excel Job failed: ' . $e->getMessage());
        }
    }
}
