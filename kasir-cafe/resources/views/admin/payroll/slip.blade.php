@extends('layouts.dashboard')

@section('content')
    <div class="flex items-center justify-between mb-4 print:hidden">
        <h1 class="text-xl font-semibold">Slip Gaji</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.index' : 'admin.payroll.index') }}"
               class="px-3 py-2 rounded-md border text-sm hover:bg-gray-50">
                Kembali
            </a>
            <button onclick="window.print()"
                    class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                Cetak Slip
            </button>
        </div>
    </div>

    @php
        $settings = \App\Models\Setting::first();
        $totalTambah = (float) $payroll->overtime_amount + (float) $payroll->bonus_amount;
    @endphp

    <div id="slip-payroll" class="max-w-2xl bg-white border rounded-lg p-6 mx-auto text-sm slip-80mm">
        <div class="text-center border-b pb-2 mb-2">
            <div class="font-bold text-base">{{ $settings->restaurant_name ?? 'n2N Cafe Resto' }}</div>
            <div class="text-[11px] text-gray-600">{{ $settings->restaurant_address ?? '-' }}</div>
            <div class="text-[11px] text-gray-600">Telp: {{ $settings->restaurant_phone ?? '-' }}</div>
            <div class="text-[11px] mt-1 font-semibold">SLIP GAJI</div>
            <div class="text-[11px] text-gray-600">Periode {{ optional($payroll->period_month)->format('m/Y') }}</div>
            <div class="text-[11px] text-gray-600">Status: {{ $payroll->status }}</div>
        </div>

        <div class="grid grid-cols-2 gap-2 mb-2 text-[11px] leading-tight">
            <div>
                <div class="text-gray-500">Nama Karyawan</div>
                <div class="font-semibold">{{ $payroll->employee_display_name }}</div>
            </div>
            <div class="text-right">
                <div class="text-gray-500">No Slip</div>
                <div class="font-semibold">PAY-{{ str_pad((string) $payroll->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div>
                <div class="text-gray-500">Approver</div>
                <div>{{ optional($payroll->approver)->name ?? '-' }}</div>
            </div>
            <div class="text-right">
                <div class="text-gray-500">Tanggal Bayar</div>
                <div>{{ optional($payroll->paid_at)->format('d/m/Y H:i') ?? '-' }}</div>
            </div>
        </div>

        <table class="w-full border text-[11px] leading-tight">
            <tbody>
                <tr class="border-b">
                    <td class="p-1.5">Gaji Pokok</td>
                    <td class="p-1.5 text-right">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b">
                    <td class="p-1.5">Lembur</td>
                    <td class="p-1.5 text-right">Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b">
                    <td class="p-1.5">Bonus</td>
                    <td class="p-1.5 text-right">Rp {{ number_format($payroll->bonus_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b bg-emerald-50">
                    <td class="p-1.5 font-semibold">Total Penambahan</td>
                    <td class="p-1.5 text-right font-semibold">Rp {{ number_format($totalTambah, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b">
                    <td class="p-1.5">Potongan Manual</td>
                    <td class="p-1.5 text-right">Rp {{ number_format($payroll->deduction_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b">
                    <td class="p-1.5">Potongan Makan</td>
                    <td class="p-1.5 text-right">Rp {{ number_format($payroll->meal_deduction_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b bg-amber-50">
                    <td class="p-1.5 font-semibold">Total Potongan</td>
                    <td class="p-1.5 text-right font-semibold">Rp {{ number_format($payroll->total_deduction_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-blue-50">
                    <td class="p-1.5 font-bold">Gaji Bersih (Net)</td>
                    <td class="p-1.5 text-right font-bold">Rp {{ number_format($payroll->net_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if(!empty($payroll->note) || !empty($payroll->approval_note))
            <div class="mt-2 space-y-1 text-[10px] text-gray-600 leading-tight">
                @if(!empty($payroll->note))
                    <div><span class="font-semibold">Catatan Payroll:</span> {{ $payroll->note }}</div>
                @endif
                @if(!empty($payroll->approval_note))
                    <div><span class="font-semibold">Catatan Approval:</span> {{ $payroll->approval_note }}</div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3 mt-5 text-center text-[10px]">
            <div>
                <div class="mb-8">Petugas</div>
                <div class="border-t pt-1">{{ $payroll->employee_display_name }}</div>
            </div>
            <div>
                <div class="mb-8">Penanggung Jawab</div>
                <div class="border-t pt-1">{{ optional($payroll->payer)->name ?? optional($payroll->approver)->name ?? '-' }}</div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @page {
        size: 80mm auto;
        margin: 0;
    }

    .slip-80mm {
        width: 80mm;
        max-width: 80mm;
        padding: 2mm 2.5mm;
        margin: 0 auto;
        font-family: "Courier New", Consolas, monospace;
        line-height: 1.2;
        box-sizing: border-box;
    }

    @media print {
        html, body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body * {
            visibility: hidden !important;
        }
        #slip-payroll, #slip-payroll * {
            visibility: visible !important;
        }
        #slip-payroll {
            position: absolute;
            left: 0;
            top: 0;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin: 0 !important;
            max-width: 80mm !important;
            width: 80mm !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
</style>
@endpush
