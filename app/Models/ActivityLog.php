<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id','model_type','model_id','action','description','old_values','new_values','ip_address'
    ];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
    public $timestamps = true;
    const UPDATED_AT = null; // only created_at

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function record(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        static::create([
            'user_id'    => Auth::id(),
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'action'     => $action,
            'description'=> $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}
