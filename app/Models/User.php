<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isApoteker(): bool { return in_array($this->role, ['admin', 'apoteker']); }
    public function isViewer(): bool   { return $this->role === 'viewer'; }
    public function canEdit(): bool    { return in_array($this->role, ['admin', 'apoteker']); }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
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
