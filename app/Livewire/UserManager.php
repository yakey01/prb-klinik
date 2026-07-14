<?php
namespace App\Livewire;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Computed;

class UserManager extends Component
{
    /* ── Form state ─────────────────────────────────────────────── */
    public bool   $showForm              = false;
    public ?int   $editId                = null;
    public string $name                  = '';
    public string $email                 = '';
    public string $phone                 = '';
    public string $jabatan               = '';
    public string $role                  = 'apoteker';
    public string $lingkup_obat          = 'keduanya';   // kronis | non_kronis | keduanya
    public bool   $is_active             = true;
    public string $password              = '';
    public string $password_confirmation = '';
    public bool   $ubahPassword          = false;

    /* ── List controls ──────────────────────────────────────────── */
    public string $search       = '';
    public string $filterRole   = '';
    public string $filterStatus = '';
    public string $sortBy       = 'name';
    public string $sortDir      = 'asc';

    /* ── Activity drawer ────────────────────────────────────────── */
    public ?int   $activityUserId = null;

    /* ── Generated-password reveal ──────────────────────────────── */
    public ?string $generatedPlain = null;

    public const ROLES = [
        'admin'    => 'Administrator',
        'apoteker' => 'Apoteker',
        'viewer'   => 'Viewer (Baca Saja)',
    ];

    /* ── Computed: filtered + sorted users ──────────────────────── */
    #[Computed]
    public function users()
    {
        $q = User::query();

        if (trim($this->search) !== '') {
            $s = '%' . trim($this->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s)
                  ->orWhere('phone', 'like', $s)
                  ->orWhere('jabatan', 'like', $s);
            });
        }
        if ($this->filterRole !== '') {
            $q->where('role', $this->filterRole);
        }
        if ($this->filterStatus !== '') {
            $q->where('is_active', $this->filterStatus === 'active');
        }

        $sortable = ['name', 'role', 'last_login_at', 'created_at'];
        $col = in_array($this->sortBy, $sortable) ? $this->sortBy : 'name';
        $q->orderBy($col, $this->sortDir === 'desc' ? 'desc' : 'asc');

        return $q->get();
    }

    /* ── Computed: headline stats ───────────────────────────────── */
    #[Computed]
    public function stats(): array
    {
        return [
            'total'    => User::count(),
            'active'   => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admin'    => User::where('role', 'admin')->count(),
            'apoteker' => User::where('role', 'apoteker')->count(),
            'viewer'   => User::where('role', 'viewer')->count(),
        ];
    }

    /* ── Computed: activity for the drawer ──────────────────────── */
    #[Computed]
    public function activityUser()
    {
        return $this->activityUserId ? User::find($this->activityUserId) : null;
    }

    #[Computed]
    public function activityLogs()
    {
        if (!$this->activityUserId) {
            return collect();
        }
        return ActivityLog::where('user_id', $this->activityUserId)
            ->latest('created_at')->limit(25)->get();
    }

    /* ── Guard ──────────────────────────────────────────────────── */
    private function ensureAdmin(): bool
    {
        if (!auth()->user()?->isAdmin()) {
            $this->dispatch('toast', message: 'Hanya administrator yang dapat mengelola pengguna.', type: 'error');
            return false;
        }
        return true;
    }

    /* ── Sorting ────────────────────────────────────────────────── */
    public function sort(string $col): void
    {
        if ($this->sortBy === $col) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $col;
            $this->sortDir = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterRole', 'filterStatus']);
        $this->sortBy = 'name';
        $this->sortDir = 'asc';
    }

    /* ── Create / Edit ──────────────────────────────────────────── */
    public function openAdd(): void
    {
        if (!$this->ensureAdmin()) return;
        $this->resetForm();
        $this->ubahPassword = true;
        $this->generatedPlain = null;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        if (!$this->ensureAdmin()) return;
        $user = User::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->phone     = (string) $user->phone;
        $this->jabatan   = (string) $user->jabatan;
        $this->role      = $user->role;
        $this->lingkup_obat = $user->lingkup_obat ?: 'keduanya';
        $this->is_active = (bool) $user->is_active;
        $this->password  = '';
        $this->password_confirmation = '';
        $this->ubahPassword = false;
        $this->generatedPlain = null;
        $this->showForm  = true;
    }

    public function generatePassword(): void
    {
        // Strong, readable temp password.
        $pwd = Str::upper(Str::random(1))
             . Str::lower(Str::random(5))
             . random_int(100, 999)
             . collect(['!', '@', '#', '$', '%'])->random();
        $this->password = $pwd;
        $this->password_confirmation = $pwd;
        $this->ubahPassword = true;
        $this->dispatch('password-generated', value: $pwd);
    }

    public function save(): void
    {
        if (!$this->ensureAdmin()) return;

        $emailRule = 'required|email|max:150|unique:users,email'
            . ($this->editId ? ",{$this->editId}" : '');

        $rules = [
            'name'    => 'required|min:2|max:120',
            'email'   => $emailRule,
            'phone'   => 'nullable|string|max:30',
            'jabatan' => 'nullable|string|max:120',
            'role'    => 'required|in:admin,apoteker,viewer',
            'lingkup_obat' => 'required|in:kronis,non_kronis,keduanya',
        ];
        if (!$this->editId || $this->ubahPassword) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $messages = [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'name.min'          => 'Nama minimal :min karakter.',
            'name.max'          => 'Nama maksimal :max karakter.',
            'email.required'    => 'Email login wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah dipakai pengguna lain.',
            'email.max'         => 'Email maksimal :max karakter.',
            'phone.max'         => 'Nomor telepon maksimal :max karakter.',
            'jabatan.max'       => 'Jabatan maksimal :max karakter.',
            'role.required'     => 'Peran wajib dipilih.',
            'role.in'           => 'Peran tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed'=> 'Konfirmasi kata sandi tidak cocok.',
            'password.min'      => 'Kata sandi minimal :min karakter.',
        ];

        $this->validate($rules, $messages);

        if ($this->editId) {
            // Don't let an admin lock themselves out by self-suspending or self-demoting.
            if ($this->editId === Auth::id()) {
                $this->is_active = true;
                $this->role = 'admin';
            }

            $u = User::findOrFail($this->editId);
            $old = $u->only('name', 'email', 'phone', 'jabatan', 'role', 'is_active');
            $data = [
                'name'         => $this->name,
                'email'        => $this->email,
                'phone'        => $this->phone ?: null,
                'jabatan'      => $this->jabatan ?: null,
                'role'         => $this->role,
                'lingkup_obat' => $this->lingkup_obat,
                'is_active'    => $this->is_active,
            ];
            if ($this->ubahPassword && $this->password) {
                $data['password'] = Hash::make($this->password);
            }
            $u->update($data);
            ActivityLog::record('updated', "User diperbarui: {$this->name}", 'User', $this->editId, $old, $data);
            $this->dispatch('toast', message: "Data \"{$this->name}\" diperbarui.", type: 'success');
        } else {
            $u = User::create([
                'name'         => $this->name,
                'email'        => $this->email,
                'phone'        => $this->phone ?: null,
                'jabatan'      => $this->jabatan ?: null,
                'role'         => $this->role,
                'lingkup_obat' => $this->lingkup_obat,
                'is_active'    => $this->is_active,
                'password'     => Hash::make($this->password),
                'created_by'   => Auth::id(),
            ]);
            ActivityLog::record('created', "User ditambah: {$this->name} ({$this->role})", 'User', $u->id);
            $this->dispatch('toast', message: "Pengguna \"{$this->name}\" berhasil dibuat.", type: 'success');
        }
        $this->cancel();
    }

    /* ── Inline actions ─────────────────────────────────────────── */
    public function toggleStatus(int $id): void
    {
        if (!$this->ensureAdmin()) return;
        if ($id === Auth::id()) {
            $this->dispatch('toast', message: 'Tidak bisa menonaktifkan akun Anda sendiri.', type: 'error');
            return;
        }
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->saveQuietly();
        $state = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::record('updated', "Status user {$state}: {$user->name}", 'User', $id);
        $this->dispatch('toast', message: "Akun \"{$user->name}\" {$state}.", type: 'success');
    }

    public function resetPassword(int $id): void
    {
        if (!$this->ensureAdmin()) return;
        $user = User::findOrFail($id);
        $plain = Str::upper(Str::random(1)) . Str::lower(Str::random(5)) . random_int(100, 999)
               . collect(['!', '@', '#', '$', '%'])->random();
        $user->password = Hash::make($plain);
        $user->saveQuietly();
        ActivityLog::record('updated', "Password user direset: {$user->name}", 'User', $id);
        $this->generatedPlain = $plain;
        $this->dispatch('toast', message: "Password \"{$user->name}\" direset.", type: 'success');
        $this->dispatch('password-reset-done', name: $user->name, value: $plain);
    }

    public function deleteUser(int $id): void
    {
        if (!$this->ensureAdmin()) return;
        if ($id === Auth::id()) {
            $this->dispatch('toast', message: 'Tidak bisa menghapus akun yang sedang login.', type: 'error');
            return;
        }
        $user = User::findOrFail($id);
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            $this->dispatch('toast', message: 'Tidak bisa menghapus admin terakhir.', type: 'error');
            return;
        }
        $name = $user->name;
        ActivityLog::record('deleted', "User dihapus: {$name}", 'User', $id);
        $user->delete();
        $this->dispatch('toast', message: "Pengguna \"{$name}\" dihapus.", type: 'success');
    }

    /* ── Activity drawer ────────────────────────────────────────── */
    public function viewActivity(int $id): void
    {
        $this->activityUserId = $id;
    }

    public function closeActivity(): void
    {
        $this->activityUserId = null;
    }

    /* ── Helpers ────────────────────────────────────────────────── */
    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editId    = null;
        $this->name      = '';
        $this->email     = '';
        $this->phone     = '';
        $this->jabatan   = '';
        $this->role      = 'apoteker';
        $this->lingkup_obat = 'keduanya';
        $this->is_active = true;
        $this->password  = '';
        $this->password_confirmation = '';
        $this->ubahPassword = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.user-manager');
    }
}
