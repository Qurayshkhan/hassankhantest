<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\DeliveryChallan;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Ses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait Utility
{
    public function getChallanByChallanNumber($number)
    {
        return DeliveryChallan::where('challan_number', $number)->first();
    }

    public function generateChallanNumber(): string
    {
        $prefix = Carbon::now()->format('dmY');

        return DB::transaction(function () use ($prefix) {
            // Find the latest challan created today with this date prefix
            $latestChallan = DeliveryChallan::where('id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($latestChallan) {
                $currentSequence = (int) substr($latestChallan->id, strlen($prefix));
                $nextSequence = $currentSequence + 1;
            }

            do {
                // Minimum width of 3 (001, 002, ..., 999), grows naturally to 1000, 1001, 100001+
                $challanNumber = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
                $nextSequence++;
            } while ($this->getChallanByChallanNumber($challanNumber) !== null);

            return $challanNumber;
        });
    }
    public function getQuotationByNumber($number)
    {
        return Quotation::where('quotation_number', $number)->first();
    }

    public function generateQuotationNumber(): string
    {
        $prefix = Carbon::now()->format('dmY');

        return DB::transaction(function () use ($prefix) {

            $latestQoutation = Quotation::where('id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($latestQoutation) {
                $currentSequence = (int) substr($latestQoutation->id, strlen($prefix));
                $nextSequence = $currentSequence + 1;
            }

            do {
                $challanNumber = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
                $nextSequence++;
            } while ($this->getQuotationByNumber($challanNumber) !== null);

            return $challanNumber;
        });
    }

    public function getCompanies()
    {
        return Company::all();
    }

    public function generateDocumentNumber()
    {
        $prefix = Carbon::now()->format('dmY');

        return DB::transaction(function () use ($prefix) {

            $latestSes = Ses::where('id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($latestSes) {
                $currentSequence = (int) substr($latestSes->id, strlen($prefix));
                $nextSequence = $currentSequence + 1;
            }

            do {
                $challanNumber = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
                $nextSequence++;
            } while ($this->getSesDocumentByNumber($challanNumber) !== null);

            return $challanNumber;
        });
    }

    public function getSesDocumentByNumber($number)
    {
        return Ses::where('document_number', $number)->first();
    }

    public function getInvoiceByNumber($number)
    {
        return Invoice::where('invoice_number', $number)->first();
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = Carbon::now()->format('dmY');

        return DB::transaction(function () use ($prefix) {
            $latestInvoice = Invoice::where('id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;

            if ($latestInvoice) {
                $currentSequence = (int) substr($latestInvoice->id, strlen($prefix));
                $nextSequence = $currentSequence + 1;
            }

            do {
                $invoiceNumber = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
                $nextSequence++;
            } while ($this->getInvoiceByNumber($invoiceNumber) !== null);

            return $invoiceNumber;
        });
    }
}
