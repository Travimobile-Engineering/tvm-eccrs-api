<?php

namespace App\Traits;

use App\Exports\ManifestReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

trait ReportTrait
{
    public function exportManifestReportToPdf($data)
    {
        $pdf = Pdf::loadView('exports.manifest_report', ['data' => $data]);

        return $pdf->download('manifest_report.pdf');
    }

    public function exportManifestReportToExcel($data)
    {
        return Excel::download(new ManifestReportExport($data), 'manifest_report.xlsx');
    }

    public function exportManifestReportToCsv($data)
    {
        return Excel::download(new ManifestReportExport($data), 'manifest_report.csv', ExcelFormat::CSV);
    }
}
