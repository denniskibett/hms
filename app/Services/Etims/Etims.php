<?php

namespace App\Services\Etims;

use App\Helpers\SystemHelper;
use App\Models\EtimsReceipt;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * KRA eTIMS (OSCU, online sales control unit). When an invoice is paid in
 * full we send the sale to KRA and keep the signed receipt it returns, which
 * is printed with a QR code on the invoice and receipt.
 *
 * Settings: system.settings.etims = enabled, environment (sandbox|production),
 * bhf_id (branch, "00" for head office), device_serial, cmc_key (encrypted,
 * from "Initialise device"). The TIN is the hotel's KRA PIN (Billing tab).
 *
 * Tax types: B = VAT 16% (standard), A = exempt. Room, food and drink lines
 * go as B. The tourism/catering levy is not VAT; it stays inside the price.
 * Confirm the item classification codes with the hotel's tax agent.
 */
class Etims
{
    public const SANDBOX = 'https://etims-api-sbx.kra.go.ke/etims-api';
    public const PRODUCTION = 'https://etims-api.kra.go.ke/etims-api';

    // Default UNSPSC-style classification and unit codes; override per line later if KRA asks.
    public const ITEM_CLASS = ['room' => '90111500', 'food' => '90101500', 'service' => '90101700'];

    public static function config(): array
    {
        $c = (array) SystemHelper::get('settings.etims', []);
        if (! empty($c['cmc_key'])) {
            try {
                $c['cmc_key'] = Crypt::decryptString($c['cmc_key']);
            } catch (\Throwable) {
                $c['cmc_key'] = null;
            }
        }

        return $c + ['enabled' => false, 'environment' => 'sandbox', 'bhf_id' => '00'];
    }

    public static function enabled(): bool
    {
        $c = self::config();

        return ! empty($c['enabled']) && SystemHelper::kraPin() && ! empty($c['cmc_key']);
    }

    public static function verifyUrl(?string $signature): ?string
    {
        if (! $signature) {
            return null;
        }
        $c = self::config();
        $host = ($c['environment'] ?? 'sandbox') === 'production' ? 'https://etims.kra.go.ke' : 'https://etims-sbx.kra.go.ke';

        return $host . '/common/link/etims/receipt/indexEtimsReceiptData?Data=' . SystemHelper::kraPin() . ($c['bhf_id'] ?? '00') . $signature;
    }

    /**
     * One-off: register this device with KRA and keep the communication key.
     */
    public function initialise(): string
    {
        $c = self::config();
        $res = Http::acceptJson()->asJson()->timeout(30)->post($this->base() . '/selectInitOsdcInfo', [
            'tin' => SystemHelper::kraPin(),
            'bhfId' => $c['bhf_id'] ?? '00',
            'dvcSrlNo' => $c['device_serial'] ?? '',
        ]);

        if ($res->json('resultCd') !== '000' || ! $res->json('data.info.cmcKey')) {
            throw new \RuntimeException('KRA did not register the device: ' . ($res->json('resultMsg') ?? 'error ' . $res->status()));
        }

        return (string) $res->json('data.info.cmcKey');
    }

    /**
     * Send a paid invoice to KRA (once). Safe to call again: a sent receipt is left alone.
     */
    public function submit(Invoice $invoice): ?EtimsReceipt
    {
        if (! self::enabled() || $invoice->status !== 'paid') {
            return null;
        }

        $receipt = DB::transaction(function () use ($invoice) {
            $existing = EtimsReceipt::where('invoice_id', $invoice->id)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            return EtimsReceipt::create([
                'invoice_id' => $invoice->id,
                'invc_no' => ((int) EtimsReceipt::lockForUpdate()->max('invc_no')) + 1,
                'status' => 'pending',
            ]);
        });

        if ($receipt->isSent()) {
            return $receipt;
        }

        $payload = $this->salePayload($invoice->loadMissing('items', 'payments'), $receipt->invc_no);
        $c = self::config();

        try {
            $res = Http::acceptJson()->asJson()->timeout(30)
                ->withHeaders(['tin' => SystemHelper::kraPin(), 'bhfId' => $c['bhf_id'] ?? '00', 'cmcKey' => $c['cmc_key']])
                ->post($this->base() . '/saveTrnsSalesOsdc', $payload);
            $ok = $res->json('resultCd') === '000';
            $data = (array) $res->json('data', []);

            $receipt->update([
                'status' => $ok ? 'sent' : 'failed',
                'attempts' => $receipt->attempts + 1,
                'request' => $payload,
                'response' => $res->json(),
                'error' => $ok ? null : ($res->json('resultMsg') ?? 'HTTP ' . $res->status()),
                'rcpt_no' => $data['rcptNo'] ?? null,
                'tot_rcpt_no' => $data['totRcptNo'] ?? null,
                'intrl_data' => $data['intrlData'] ?? null,
                'rcpt_sign' => $data['rcptSign'] ?? null,
                'sdc_id' => $data['sdcId'] ?? null,
                'mrc_no' => $data['mrcNo'] ?? null,
                'receipt_at' => $ok ? now() : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('eTIMS submission failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            $receipt->update(['status' => 'failed', 'attempts' => $receipt->attempts + 1, 'request' => $payload, 'error' => $e->getMessage()]);
        }

        return $receipt->fresh();
    }

    /**
     * KRA's sales payload. Lines are scaled so they add up to the invoice total
     * after any discount; VAT is taken out of the VAT-inclusive amount.
     */
    public function salePayload(Invoice $invoice, int $invcNo): array
    {
        $vatRate = (float) $invoice->vat_rate;
        $gross = (float) $invoice->items->sum(fn (InvoiceItem $i) => (float) $i->quantity * (float) $i->unit_price);
        $total = (float) $invoice->total;
        $scale = $gross > 0 ? $total / $gross : 1;
        $now = now('Africa/Nairobi');

        $items = $invoice->items->values()->map(function (InvoiceItem $item, int $i) use ($scale, $vatRate) {
            $qty = (float) $item->quantity ?: 1;
            $amount = round((float) $item->quantity * (float) $item->unit_price * $scale, 2);
            $tax = $vatRate > 0 ? round($amount * $vatRate / (100 + $vatRate), 2) : 0;
            $class = match (true) {
                str_contains((string) $item->source_type, 'room') || str_contains(strtolower($item->description), 'night') => 'room',
                str_contains((string) $item->source_type, 'kitchen') || str_contains((string) $item->source_type, 'menu') => 'food',
                default => 'service',
            };

            return [
                'itemSeq' => $i + 1,
                'itemCd' => 'KE2NTU' . str_pad((string) ($item->id % 10000000), 7, '0', STR_PAD_LEFT),
                'itemClsCd' => self::ITEM_CLASS[$class],
                'itemNm' => mb_substr($item->description, 0, 200),
                'pkgUnitCd' => 'NT',
                'pkg' => 1,
                'qtyUnitCd' => 'U',
                'qty' => $qty,
                'prc' => round($amount / $qty, 2),
                'splyAmt' => $amount,
                'dcRt' => 0,
                'dcAmt' => 0,
                'taxTyCd' => $vatRate > 0 ? 'B' : 'A',
                'taxblAmt' => $amount,
                'taxAmt' => $tax,
                'totAmt' => $amount,
            ];
        });

        $taxable = round($items->sum('taxblAmt'), 2);
        $taxAmt = round($items->sum('taxAmt'), 2);
        $code = $vatRate > 0 ? 'B' : 'A';
        $method = optional($invoice->payments->where('status', 'completed')->sortByDesc('amount')->first())->method;

        return [
            'invcNo' => $invcNo,
            'orgInvcNo' => 0,
            'custTin' => null,
            'custNm' => mb_substr((string) ($invoice->stay?->guest?->name ?? 'Walk-in guest'), 0, 60),
            'salesTyCd' => 'N',      // normal sale
            'rcptTyCd' => 'S',       // sale receipt
            'pmtTyCd' => match ($method) { 'cash' => '01', 'credit_card' => '05', 'mobile_money' => '06', 'bank_transfer' => '07', default => '02' },
            'salesSttsCd' => '02',   // approved
            'cfmDt' => $now->format('YmdHis'),
            'salesDt' => $now->format('Ymd'),
            'totItemCnt' => $items->count(),
            'taxblAmtA' => $code === 'A' ? $taxable : 0, 'taxblAmtB' => $code === 'B' ? $taxable : 0, 'taxblAmtC' => 0, 'taxblAmtD' => 0, 'taxblAmtE' => 0,
            'taxRtA' => 0, 'taxRtB' => $vatRate, 'taxRtC' => 0, 'taxRtD' => 0, 'taxRtE' => 8,
            'taxAmtA' => 0, 'taxAmtB' => $code === 'B' ? $taxAmt : 0, 'taxAmtC' => 0, 'taxAmtD' => 0, 'taxAmtE' => 0,
            'totTaxblAmt' => $taxable,
            'totTaxAmt' => $taxAmt,
            'totAmt' => $taxable,
            'prchrAcptcYn' => 'N',
            'remark' => $invoice->invoice_number,
            'regrId' => 'hms', 'regrNm' => 'hms', 'modrId' => 'hms', 'modrNm' => 'hms',
            'receipt' => ['rcptPbctDt' => $now->format('YmdHis'), 'prchrAcptcYn' => 'N'],
            'itemList' => $items->all(),
        ];
    }

    private function base(): string
    {
        return (self::config()['environment'] ?? 'sandbox') === 'production' ? self::PRODUCTION : self::SANDBOX;
    }
}
