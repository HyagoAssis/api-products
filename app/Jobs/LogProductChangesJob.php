<?php

namespace App\Jobs;

use App\Models\UserLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogProductChangesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function __construct(
        public string $modelType,
        public int $modelId,
        public ?int $userId,
        public string $operation,
        public ?array $oldValues = null,
        public ?array $newValues = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UserLog::create([
            'user_id' => $this->userId,
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
            'operation' => $this->operation,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
        ]);
    }
}
