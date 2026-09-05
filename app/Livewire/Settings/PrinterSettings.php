<?php

namespace App\Livewire\Settings;

use App\Domains\MasterData\Models\AppSetting;
use Livewire\Component;

class PrinterSettings extends Component
{
    public string $reportPrinterName = '';
    public string $reportPaperSize = 'A4';
    public string $reportOrientation = 'portrait';

    public string $receiptPrinterName = '';
    public string $receiptPaperWidth = '58';
    public int $receiptFontSize = 12;
    public bool $receiptAutoCut = true;

    public function mount(): void
    {
        $saved = AppSetting::getMany([
            'printer.report_name',
            'printer.report_paper_size',
            'printer.report_orientation',
            'printer.receipt_name',
            'printer.receipt_paper_width',
            'printer.receipt_font_size',
            'printer.receipt_auto_cut',
        ], [
            'printer.report_name'         => '',
            'printer.report_paper_size'   => 'A4',
            'printer.report_orientation'  => 'portrait',
            'printer.receipt_name'        => '',
            'printer.receipt_paper_width' => '58',
            'printer.receipt_font_size'   => 12,
            'printer.receipt_auto_cut'    => true,
        ]);

        $this->reportPrinterName  = (string) $saved['printer.report_name'];
        $this->reportPaperSize    = (string) $saved['printer.report_paper_size'];
        $this->reportOrientation  = (string) $saved['printer.report_orientation'];
        $this->receiptPrinterName = (string) $saved['printer.receipt_name'];
        $this->receiptPaperWidth  = (string) $saved['printer.receipt_paper_width'];
        $this->receiptFontSize    = (int) $saved['printer.receipt_font_size'];
        $this->receiptAutoCut     = (bool) $saved['printer.receipt_auto_cut'];
    }

    protected function rules(): array
    {
        return [
            'reportPrinterName'  => ['nullable', 'string', 'max:100'],
            'reportPaperSize'    => ['required', 'in:A4,Letter,F4'],
            'reportOrientation'  => ['required', 'in:portrait,landscape'],
            'receiptPrinterName' => ['nullable', 'string', 'max:100'],
            'receiptPaperWidth'  => ['required', 'in:58,80'],
            'receiptFontSize'    => ['required', 'integer', 'min:8', 'max:20'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        AppSetting::setMany([
            'printer.report_name'         => trim($this->reportPrinterName),
            'printer.report_paper_size'   => $this->reportPaperSize,
            'printer.report_orientation'  => $this->reportOrientation,
            'printer.receipt_name'        => trim($this->receiptPrinterName),
            'printer.receipt_paper_width' => $this->receiptPaperWidth,
            'printer.receipt_font_size'   => $this->receiptFontSize,
            'printer.receipt_auto_cut'    => $this->receiptAutoCut,
        ]);

        session()->flash('sukses', 'Pengaturan printer berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.settings.printer-settings')
            ->layout('layouts.app', ['title' => 'Pengaturan Printer']);
    }
}
