<?php

namespace App\Http\Middleware;

use App\Domains\MasterData\Services\RoleRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesi 11: Middleware role di level route.
 *
 * Sebelumnya, aturan akses per role (RoleRegistry::access(), diatur lewat
 * /pengaturan/role atau fallback ke config/roles.php) hanya dipakai untuk
 * menyembunyikan menu di sidebar (resources/views/components/sidebar-addons.blade.php).
 * User yang tahu alamat URL langsung tetap bisa membuka halaman yang
 * seharusnya tidak boleh diaksesnya. Middleware ini menutup celah itu dengan
 * mengecek ulang aturan yang sama di level route, dengan logika fallback yang
 * identik supaya perilakunya konsisten dengan menu sidebar.
 */
class EnsureRoleHasAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $hasRoleColumn = false;
        try {
            $hasRoleColumn = Schema::hasColumn('users', 'role');
        } catch (\Throwable $e) {
            $hasRoleColumn = false;
        }

        // Sama seperti sidebar-addons.blade.php: kalau kolom role belum ada
        // dan mode longgar aktif, jangan blokir apa pun supaya sistem tidak
        // terlihat "terkunci" sebelum migration dijalankan.
        if (! $hasRoleColumn) {
            if (config('roles.permissive_when_no_role_column', true)) {
                return $next($request);
            }

            abort(403, 'Data role pengguna belum lengkap. Hubungi administrator.');
        }

        $routeName = $request->route()?->getName();
        if (! $routeName) {
            return $next($request);
        }

        $access  = RoleRegistry::access();
        $allowed = $access[$routeName] ?? null;

        // Route tanpa aturan akses spesifik dianggap terbuka untuk semua user
        // login, sama seperti perilaku menu sidebar sebelumnya.
        if ($allowed === null) {
            return $next($request);
        }

        $userRole = strtolower((string) ($user->role ?? ''));

        if (! in_array($userRole, $allowed, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
