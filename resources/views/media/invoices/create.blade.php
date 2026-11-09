<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="simedkom-label text-cyan-600">Administrasi tagihan</p>
            <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Ajukan invoice</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" enctype="multipart/form-data" action="{{ route('media.invoices.store') }}" class="simedkom-card space-y-6 p-6 sm:p-8">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="simedkom-label" for="contract_id">Kontrak</label>
                    <select id="contract_id" name="contract_id" class="simedkom-input mt-2" required>
                        <option value="">Pilih kontrak</option>
                        @foreach ($contracts as $contract)
                            <option value="{{ $contract->id }}">
                                {{ $contract->nomor_kontrak }} · {{ $contract->kuota_terpakai }} publikasi valid
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="invoice_number">Nomor invoice</label>
                        <input id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" class="simedkom-input mt-2" required>
                    </div>
                    <div>
                        <label class="simedkom-label" for="invoice_date">Tanggal invoice</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date') }}" class="simedkom-input mt-2" required>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="period_start">Periode mulai</label>
                        <input type="date" id="period_start" name="period_start" value="{{ old('period_start') }}" class="simedkom-input mt-2" required>
                    </div>
                    <div>
                        <label class="simedkom-label" for="period_end">Periode berakhir</label>
                        <input type="date" id="period_end" name="period_end" value="{{ old('period_end') }}" class="simedkom-input mt-2" required>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label class="simedkom-label" for="subtotal">Subtotal</label>
                        <input type="number" step="0.01" name="subtotal" id="subtotal" class="simedkom-input mt-2" required>
                    </div>
                    <div>
                        <label class="simedkom-label" for="tax">Pajak</label>
                        <input type="number" step="0.01" name="tax" id="tax" class="simedkom-input mt-2" value="0" required>
                    </div>
                    <div>
                        <label class="simedkom-label" for="total">Total</label>
                        <input type="number" step="0.01" name="total" id="total" class="simedkom-input mt-2" required>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="simedkom-label" for="invoice">Invoice digital</label>
                        <input type="file" name="invoice" id="invoice" accept=".pdf,.jpg,.jpeg,.png" class="simedkom-input mt-2" required>
                    </div>
                    <div>
                        <label class="simedkom-label" for="tax_document">Dokumen pajak</label>
                        <input type="file" name="tax_document" id="tax_document" accept=".pdf,.jpg,.jpeg,.png" class="simedkom-input mt-2">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">Kirim invoice</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
