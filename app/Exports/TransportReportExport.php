<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class TransportReportExport implements FromCollection
{
    public function __construct(
        protected $data
    ) {}

    public function collection()
    {
        return collect($this->data);
    }
}
