<?php

namespace App\Services;

use App\Enums\DebtType;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class CreditReportExporter
{
    private const CHUNK_SIZE = 500;

    private const HEADERS = [
        'ID',
        'Nombre Completo',
        'DNI',
        'Email',
        'Telefono',
        'Compania',
        'Tipo de deuda',
        'Situacion',
        'Atraso',
        'Entidad',
        'Monto total',
        'Linea total',
        'Linea usada',
        'Reporte subido el',
        'Estado',
    ];

    /**
     * Export credit reports to an XLSX file using streaming to minimize memory usage.
     *
     * Uses UNION ALL queries to combine all debt types into a single result set,
     * then processes results in chunks via cursor-based iteration. OpenSpout writes
     * rows directly to disk without buffering the full spreadsheet in memory.
     */
    public function export(?string $dateFrom, ?string $dateTo): string
    {
        $filePath = storage_path('app/private/credit_report_' . now()->format('Y_m_d_His') . '.xlsx');

        $writer = new Writer();
        $writer->openToFile($filePath);

        $this->writeHeaderRow($writer);
        $this->writeDataRows($writer, $dateFrom, $dateTo);

        $writer->close();

        return $filePath;
    }

    private function writeHeaderRow(Writer $writer): void
    {
        $headerStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(11);

        $writer->addRow(new Row(
            array_map(
                fn (string $header) => Cell::fromValue($header)->withStyle($headerStyle),
                self::HEADERS
            ),
        ));
    }

    /**
     * Build a unified query that combines loans, other debts, and credit cards
     * into a single stream. Uses lazy() to iterate via cursor without loading
     * all records into memory at once.
     */
    private function writeDataRows(Writer $writer, ?string $dateFrom, ?string $dateTo): void
    {
        $query = $this->buildUnifiedQuery($dateFrom, $dateTo);

        $query->orderBy('report_id')->lazy(self::CHUNK_SIZE)->each(
            fn (object $row) => $writer->addRow(new Row([
                Cell::fromValue($row->report_id),
                Cell::fromValue($row->full_name),
                Cell::fromValue($row->document),
                Cell::fromValue($row->email),
                Cell::fromValue($row->phone),
                Cell::fromValue($row->company),
                Cell::fromValue($row->debt_type),
                Cell::fromValue($row->situation),
                Cell::fromValue($row->arrears),
                Cell::fromValue($row->entity),
                Cell::fromValue((float) $row->total_amount),
                Cell::fromValue($row->total_line !== null ? (float) $row->total_line : ''),
                Cell::fromValue($row->used_line !== null ? (float) $row->used_line : ''),
                Cell::fromValue($row->report_date),
                Cell::fromValue($row->status),
            ]))
        );
    }

    /**
     * Combine all three debt sources (loans, other debts, credit cards) via UNION ALL.
     * This avoids N+1 queries and processes all debt types in a single pass.
     */
    private function buildUnifiedQuery(?string $dateFrom, ?string $dateTo)
    {
        $loansQuery = DB::table('report_loans')
            ->join('subscription_reports', 'subscription_reports.id', '=', 'report_loans.subscription_report_id')
            ->join('subscriptions', 'subscriptions.id', '=', 'subscription_reports.subscription_id')
            ->select([
                'subscription_reports.id as report_id',
                'subscriptions.full_name',
                'subscriptions.document',
                'subscriptions.email',
                'subscriptions.phone',
                DB::raw("report_loans.bank as company"),
                DB::raw("'" . DebtType::LOAN->value . "'::text as debt_type"),
                DB::raw("report_loans.status::text as situation"),
                DB::raw("report_loans.expiration_days as arrears"),
                DB::raw("report_loans.bank as entity"),
                DB::raw("report_loans.amount as total_amount"),
                DB::raw("NULL::numeric as total_line"),
                DB::raw("NULL::numeric as used_line"),
                DB::raw("subscription_reports.created_at as report_date"),
                DB::raw("report_loans.status::text as status"),
            ]);

        $otherDebtsQuery = DB::table('report_other_debts')
            ->join('subscription_reports', 'subscription_reports.id', '=', 'report_other_debts.subscription_report_id')
            ->join('subscriptions', 'subscriptions.id', '=', 'subscription_reports.subscription_id')
            ->select([
                'subscription_reports.id as report_id',
                'subscriptions.full_name',
                'subscriptions.document',
                'subscriptions.email',
                'subscriptions.phone',
                DB::raw("report_other_debts.entity as company"),
                DB::raw("'" . DebtType::OTHER_DEBT->value . "'::text as debt_type"),
                DB::raw("NULL::text as situation"),
                DB::raw("report_other_debts.expiration_days as arrears"),
                DB::raw("report_other_debts.entity as entity"),
                DB::raw("report_other_debts.amount as total_amount"),
                DB::raw("NULL::numeric as total_line"),
                DB::raw("NULL::numeric as used_line"),
                DB::raw("subscription_reports.created_at as report_date"),
                DB::raw("CASE WHEN report_other_debts.expiration_days > 0 THEN 'VENCIDO' ELSE 'VIGENTE' END as status"),
            ]);

        $creditCardsQuery = DB::table('report_credit_cards')
            ->join('subscription_reports', 'subscription_reports.id', '=', 'report_credit_cards.subscription_report_id')
            ->join('subscriptions', 'subscriptions.id', '=', 'subscription_reports.subscription_id')
            ->select([
                'subscription_reports.id as report_id',
                'subscriptions.full_name',
                'subscriptions.document',
                'subscriptions.email',
                'subscriptions.phone',
                DB::raw("report_credit_cards.bank as company"),
                DB::raw("'" . DebtType::CREDIT_CARD->value . "'::text as debt_type"),
                DB::raw("NULL::text as situation"),
                DB::raw("0 as arrears"),
                DB::raw("report_credit_cards.bank as entity"),
                DB::raw("report_credit_cards.used as total_amount"),
                DB::raw("report_credit_cards.line as total_line"),
                DB::raw("report_credit_cards.used as used_line"),
                DB::raw("subscription_reports.created_at as report_date"),
                DB::raw("CASE WHEN report_credit_cards.used > report_credit_cards.line * 0.9 THEN 'ALTO USO' ELSE 'NORMAL' END as status"),
            ]);

        if ($dateFrom) {
            $loansQuery->where('subscription_reports.created_at', '>=', $dateFrom);
            $otherDebtsQuery->where('subscription_reports.created_at', '>=', $dateFrom);
            $creditCardsQuery->where('subscription_reports.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $loansQuery->where('subscription_reports.created_at', '<=', $dateTo . ' 23:59:59');
            $otherDebtsQuery->where('subscription_reports.created_at', '<=', $dateTo . ' 23:59:59');
            $creditCardsQuery->where('subscription_reports.created_at', '<=', $dateTo . ' 23:59:59');
        }

        return $loansQuery->unionAll($otherDebtsQuery)->unionAll($creditCardsQuery);
    }
}
