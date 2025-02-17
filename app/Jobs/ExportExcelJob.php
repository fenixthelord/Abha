<?php

namespace App\Jobs;

use App\Http\Traits\FileExportReportTrait;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Import the trait that contains the exportData() method.

class ExportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FileExportReportTrait;

    // The model class to query.
    protected string $modelClass;
    // Filter and search criteria.
    protected array $filters;
    // Relationships to eager load.
    protected array $relations;
    // Name of the output Excel file.
    protected string $filename;
    // Array of user IDs to be notified when the export is complete.
    protected array $userIds;
    // Optional callback to transform each record into an exportable format.
    protected $transformCallback;

    /**
     * Constructor to initialize the export job.
     *
     * @param string $modelClass The model class to query (e.g., App\Models\Service).
     * @param array $filters Filtering and search criteria (e.g., ['department_id' => 5, 'search' => 'abc']).
     * @param array $relations Relationships to load (e.g., ['department']).
     * @param string $filename The name of the exported Excel file.
     * @param array $userIds User IDs that will receive the export completion notification.
     * @param callable $transformCallback Optional callback function to format each record.
     */
    public function __construct(
        string   $modelClass,
        array    $filters = [],
        array    $relations = [],
        string   $filename = 'export.xlsx',
        array    $userIds = [],
        callable $transformCallback = null
    )
    {
        $this->modelClass = $modelClass;
        $this->filters = $filters;
        $this->relations = $relations;
        $this->filename = $filename;
        $this->userIds = $userIds;
        $this->transformCallback = $transformCallback;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Build the query from the provided model class.
            $query = ($this->modelClass)::query();

            // Apply filters except for the 'search' key.
            foreach ($this->filters as $key => $value) {
                if ($key !== 'search') {
                    $query->where($key, $value);
                }
            }

            // Apply dynamic search if 'search' key exists.
            if (!empty($this->filters['search'])) {
                $searchTerm = $this->filters['search'];
                $query->where(function ($q) use ($searchTerm) {
                    // Example: search in 'name' and 'details' columns.
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('details', 'like', "%{$searchTerm}%");
                });
            }

            // Eager load any relationships if provided.
            if (!empty($this->relations)) {
                $query->with($this->relations);
            }

            // Initialize an array to collect export data.
            $exportData = [];

            // Retrieve data in chunks to manage memory usage.
            $query->chunk(100, function ($items) use (&$exportData) {
                foreach ($items as $item) {
                    // Transform each item using the callback if provided.
                    if ($this->transformCallback) {
                        $exportData[] = call_user_func($this->transformCallback, $item);
                    } else {
                        // Otherwise, convert the model instance to an array.
                        $exportData[] = $item->toArray();
                    }
                }
            });

            // Generate the Excel file and obtain its URL.
            // This method is assumed to be defined in the FileExportReportTrait.
            $excelFileUrl = $this->exportData($exportData, $this->filename);

            // Retrieve the current user ID using Sanctum authentication.
            $userId = auth('sanctum')->id();
            $dateNow = date('Ymd');

            // Define the channel name for the notification.
            $channelName = "pusher_{$userId}";

            // Prepare notification parameters.
            $params = [
                'sender_id' => null,
                'sender_type' => 'user',
                'sender_service' => 'service_user',
                'title' => 'Excel Export Completed',
                'body' => 'Your exported Excel file is ready.',
                'user_ids' => $this->userIds,
                'receiver_service' => 'service_user',
                'receiver_type' => 'user',
                'group_id' => null,
                'channel' => $channelName,
                'image' => null,
                'url' => url('storage/app/'.$excelFileUrl),
            ];

            // Send the notification using the NotificationService.
            $notificationResponse = app('App\Services\NotificationService')
                ->postCall('/notifications/send-pusher', $params);

            // Log the response from the notification service.
            Log::info('Notification API response:', ['response' => $notificationResponse]);
        } catch (\Exception $e) {
            // Log any errors encountered during the export process.
            Log::error('Export job failed: ' . $e->getMessage());
        }
    }
}
