<?php

namespace App\Models;

use App\Actions\SystemLogAction;
use App\Dtos\SystemLogData;
use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    protected $connection = 'transport';

    protected static function booted()
    {
        static::created(function ($model) {
            $dto = new SystemLogData(
                'Created new resource',
                $model,
                $model->id,
                'created',
                request()->ip(),
                null,
                $model->getAttributes(),
                request()->fullUrl()
            );

            app(SystemLogAction::class)->execute($dto);
        });

        static::updated(function ($model) {
            $dto = new SystemLogData(
                'Updated resource',
                $model,
                $model->id,
                'updated',
                request()->ip(),
                null,
                $model->getAttributes(),
                request()->fullUrl()
            );

            app(SystemLogAction::class)->execute($dto);
        });

        static::deleted(function ($model) {
            $dto = new SystemLogData(
                'Deleted resource',
                $model,
                $model->id,
                'deleted',
                request()->ip(),
                null,
                $model->getAttributes(),
                request()->fullUrl()
            );

            app(SystemLogAction::class)->execute($dto);
        });
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
