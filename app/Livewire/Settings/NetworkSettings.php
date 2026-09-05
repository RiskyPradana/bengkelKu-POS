<?php

namespace App\Livewire\Settings;

use App\Domains\MasterData\Models\AppSetting;
use Livewire\Component;

class NetworkSettings extends Component
{
    public bool $lanEnabled = true;

    public string $lanIp = '';

    public string $lanPort = '8000';

    public string $detectedIp = '';

    public function mount(): void
    {
        $saved = AppSetting::getMany(
            ['network.lan_enabled', 'network.lan_ip', 'network.lan_port'],
            [
                'network.lan_enabled' => true,
                'network.lan_ip'      => '',
                'network.lan_port'    => (string) (request()->getPort() ?: 8000),
            ]
        );

        $this->lanEnabled = (bool) $saved['network.lan_enabled'];
        $this->lanPort    = (string) $saved['network.lan_port'];
        $this->detectedIp = $this->detectLocalIp();
        $this->lanIp      = $saved['network.lan_ip'] !== '' ? (string) $saved['network.lan_ip'] : $this->detectedIp;
    }

    protected function rules(): array
    {
        return [
            'lanIp'   => ['nullable', 'string', 'max:45'],
            'lanPort' => ['required', 'string', 'max:6'],
        ];
    }

    /**
     * Mendeteksi alamat IPv4 lokal (LAN) server ini, dengan beberapa cara
     * berjenjang supaya tetap jalan di Linux/macOS, dan tetap ada fallback
     * untuk lingkungan yang membatasi shell_exec (mis. sebagian shared hosting).
     */
    protected function detectLocalIp(): string
    {
        $candidates = [];

        if (function_exists('shell_exec')) {
            $out = @shell_exec('hostname -I 2>/dev/null');
            if ($out) {
                foreach (explode(' ', trim($out)) as $ip) {
                    $ip = trim($ip);
                    if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $candidates[] = $ip;
                    }
                }
            }
        }

        if (empty($candidates)) {
            $host = gethostname();
            $ip   = $host ? gethostbyname($host) : '';
            if ($ip && $ip !== $host && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $candidates[] = $ip;
            }
        }

        foreach ($candidates as $ip) {
            if (! str_starts_with($ip, '127.')) {
                return $ip;
            }
        }

        return $candidates[0] ?? '127.0.0.1';
    }

    public function refreshDetectedIp(): void
    {
        $this->detectedIp = $this->detectLocalIp();
        session()->flash('sukses', 'IP lokal terdeteksi ulang: ' . $this->detectedIp);
    }

    public function save(): void
    {
        $this->validate();

        AppSetting::setMany([
            'network.lan_enabled' => $this->lanEnabled,
            'network.lan_ip'      => trim($this->lanIp),
            'network.lan_port'    => trim($this->lanPort),
        ]);

        session()->flash('sukses', 'Pengaturan jaringan lokal berhasil disimpan.');
    }

    public function getLanUrlProperty(): string
    {
        $ip   = $this->lanIp !== '' ? $this->lanIp : $this->detectedIp;
        $port = $this->lanPort !== '' ? $this->lanPort : '8000';

        return 'http://' . $ip . ':' . $port;
    }

    public function render()
    {
        return view('livewire.settings.network-settings')
            ->layout('layouts.app', ['title' => 'Jaringan Lokal (LAN)']);
    }
}
