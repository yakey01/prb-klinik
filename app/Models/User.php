<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'jabatan', 'password', 'role',
        'is_active', 'last_login_at', 'last_login_ip', 'login_count', 'created_by',
    ];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'login_count'       => 'integer',
        'password'          => 'hashed',
    ];

    /* ── Role metadata — single source of truth ─────────────────── */
    public const ROLE_META = [
        'admin' => [
            'label' => 'Administrator',
            'desc'  => 'Akses penuh: kelola pengguna, hapus data, semua modul.',
            'color' => '#f2c668', 'bg' => 'rgba(217,164,65,.15)', 'border' => 'rgba(217,164,65,.3)',
            'grad'  => 'linear-gradient(135deg,#d9a441 0%,#c4892e 100%)', 'ink' => '#1a0e00',
            'perms' => ['Kelola pengguna & hak akses', 'Hapus data permanen', 'Semua modul & laporan', 'Pengaturan sistem'],
        ],
        'apoteker' => [
            'label' => 'Apoteker',
            'desc'  => 'Operasional: input & edit data obat, pasien, transaksi.',
            'color' => '#6fb1e0', 'bg' => 'rgba(111,177,224,.15)', 'border' => 'rgba(111,177,224,.3)',
            'grad'  => 'linear-gradient(135deg,#6fb1e0 0%,#3d7fb5 100%)', 'ink' => '#04121f',
            'perms' => ['Input & edit data obat/pasien', 'Buat pengadaan & pengambilan', 'Lihat laporan', 'Tidak bisa hapus/kelola user'],
        ],
        'viewer' => [
            'label' => 'Viewer',
            'desc'  => 'Baca saja: melihat dashboard & laporan tanpa mengubah.',
            'color' => '#8fae9f', 'bg' => 'rgba(143,174,159,.12)', 'border' => 'rgba(143,174,159,.25)',
            'grad'  => 'linear-gradient(135deg,#5f8071 0%,#3a564a 100%)', 'ink' => '#eaf3ee',
            'perms' => ['Lihat dashboard & laporan', 'Tidak bisa input/edit', 'Tidak bisa hapus', 'Akses baca menyeluruh'],
        ],
    ];

    public static function roles(): array
    {
        return collect(self::ROLE_META)->map(fn ($m) => $m['label'])->all();
    }

    public function roleMeta(): array
    {
        return self::ROLE_META[$this->role] ?? self::ROLE_META['viewer'];
    }

    public function roleLabel(): string
    {
        return $this->roleMeta()['label'];
    }

    /* ── Permission helpers ─────────────────────────────────────── */
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isApoteker(): bool { return in_array($this->role, ['admin', 'apoteker']); }
    public function isViewer(): bool   { return $this->role === 'viewer'; }
    public function canEdit(): bool    { return $this->is_active && in_array($this->role, ['admin', 'apoteker']); }

    /* ── Presentation helpers ───────────────────────────────────── */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => !preg_match('/^(dr|drg|s\.?farm|apt|m\.?kes|sim|\.|,)/i', $p)));
        if (count($parts) === 0) {
            return strtoupper(mb_substr($this->name ?: '?', 0, 2));
        }
        if (count($parts) === 1) {
            return strtoupper(mb_substr($parts[0], 0, 2));
        }
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }

    public function avatarGradient(): string
    {
        return $this->roleMeta()['grad'];
    }

    public function statusLabel(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function lastLoginHuman(): string
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Belum pernah';
    }

    /* ── Scopes ─────────────────────────────────────────────────── */
    public function scopeActive(Builder $q): Builder   { return $q->where('is_active', true); }
    public function scopeRole(Builder $q, string $r): Builder { return $q->where('role', $r); }

    /* ── Relations ──────────────────────────────────────────────── */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function roleBadgeClass(string $role): string
    {
        return match ($role) {
            'admin'    => 'badge-laba',
            'apoteker' => 'badge-po',
            default    => 'badge-est',
        };
    }
}
