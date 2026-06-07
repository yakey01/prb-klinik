<?php
namespace App\Livewire;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Computed;

class UserManager extends Component
{
    public bool   $showForm             = false;
    public ?int   $editId               = null;
    public string $name                 = '';
    public string $email                = '';
    public string $role                 = 'admin';
    public string $password             = '';
    public string $password_confirmation= '';
    public bool   $ubahPassword         = false;

    public const ROLES = [
        'admin'    => 'Admin',
        'apoteker' => 'Apoteker',
        'viewer'   => 'Viewer (Baca Saja)',
    ];

    #[Computed]
    public function users() { return User::orderBy('name')->get(); }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->ubahPassword = true;
        $this->showForm     = true;
    }

    public function openEdit(int $id): void
    {
        $user                  = User::findOrFail($id);
        $this->editId          = $id;
        $this->name            = $user->name;
        $this->email           = $user->email;
        $this->role            = $user->role;
        $this->password        = '';
        $this->password_confirmation = '';
        $this->ubahPassword    = false;
        $this->showForm        = true;
    }

    public function save(): void
    {
        $emailRule = 'required|email|max:150|unique:users,email'
            . ($this->editId ? ",{$this->editId}" : '');

        $rules = [
            'name'  => 'required|min:2|max:120',
            'email' => $emailRule,
            'role'  => 'required|in:admin,apoteker,viewer',
        ];
        if (!$this->editId || $this->ubahPassword) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $this->validate($rules);

        if ($this->editId) {
            $old  = User::find($this->editId)->only('name','email','role');
            $data = ['name' => $this->name, 'email' => $this->email, 'role' => $this->role];
            if ($this->ubahPassword && $this->password) {
                $data['password'] = Hash::make($this->password);
            }
            User::findOrFail($this->editId)->update($data);
            ActivityLog::record('updated',"User diperbarui: {$this->name}",'User',$this->editId,$old,$data);
            $this->dispatch('toast', message: "Data \"{$this->name}\" diperbarui.", type: 'success');
        } else {
            $u = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'role'     => $this->role,
                'password' => Hash::make($this->password),
            ]);
            ActivityLog::record('created',"User ditambah: {$this->name}",'User',$u->id);
            $this->dispatch('toast', message: "Pengguna \"{$this->name}\" ditambahkan.", type: 'success');
        }
        $this->cancel();
    }

    public function deleteUser(int $id): void
    {
        if (!auth()->user()?->isAdmin()) {
            $this->dispatch('toast', message: 'Hanya admin yang dapat menghapus pengguna.', type: 'error');
            return;
        }
        if ($id === Auth::id()) {
            $this->dispatch('toast', message: 'Tidak bisa menghapus akun yang sedang login.', type: 'error');
            return;
        }
        $user = User::findOrFail($id);
        $name = $user->name;
        ActivityLog::record('deleted',"User dihapus: {$name}",'User',$id);
        $user->delete();
        $this->dispatch('toast', message: "Pengguna \"{$name}\" dihapus.", type: 'success');
    }

    public function cancel(): void { $this->showForm = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->editId                = null;
        $this->name                  = '';
        $this->email                 = '';
        $this->role                  = 'admin';
        $this->password              = '';
        $this->password_confirmation = '';
        $this->ubahPassword          = false;
        $this->resetValidation();
    }

    public function render() { return view('livewire.user-manager'); }
}
