<?php

namespace App\Actions;

use App\Dtos\SystemLogData;
use App\Models\SystemLog;

class SystemLogAction
{
    public function execute(SystemLogData $dto)
    {
        $ipAddress = request()->ip();

        SystemLog::create([
            'user_id' => $dto->userId ?? null,
            'activity' => $dto->activity,
            'model' => $dto->model,
            'model_id' => $dto->modelId,
            'ip_address' => $ipAddress,
            'user_agent' => request()->header('User-Agent'),
            'event_type' => $dto->eventType,
            'url' => request()->url(),
            'old_values' => $dto->oldValues ?? null,
            'new_values' => $dto->newValues ?? null,
        ]);
    }
}
