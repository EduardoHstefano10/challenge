<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditReportRequest;
use App\Services\CreditReportExporter;

class CreditReportController extends Controller
{
    public function __construct(
        private readonly CreditReportExporter $exporter,
    ) {}

    public function export(CreditReportRequest $request)
    {
        $filePath = $this->exporter->export(
            $request->validated('date_from'),
            $request->validated('date_to'),
        );

        return response()->download($filePath, 'reporte_crediticio.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
