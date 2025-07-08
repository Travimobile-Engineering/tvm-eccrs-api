<?php

namespace App\Dtos;

class SystemLogData
{
    public function __construct(
        public string $activity,
        public ?string $model = null,
        public $modelId = null,
        public ?string $eventType = null,
        public string $ipAddress = '',
        public ?array $oldValues = null,
        public ?array $newValues = null,
        public string $url = '',
        public ?int $userId = null,
    ) {
        $this->activity = $activity;
        $this->model = $model;
        $this->modelId = $modelId;
        $this->eventType = $eventType;
        $this->ipAddress = $ipAddress;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
        $this->url = $url;
        $this->userId = $userId;
    }
}
