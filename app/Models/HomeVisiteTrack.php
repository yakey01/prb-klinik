<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeVisiteTrack extends Model
{
    protected $table = 'home_visite_tracks';

    public $timestamps = false;

    protected $fillable = [
        'home_visite_id', 'latitude', 'longitude',
        'accuracy', 'speed', 'battery_level', 'recorded_at',
    ];

    protected $casts = [
        'latitude'      => 'float',
        'longitude'     => 'float',
        'accuracy'      => 'float',
        'speed'         => 'float',
        'battery_level' => 'integer',
        'recorded_at'   => 'datetime',
        'created_at'    => 'datetime',
    ];

    public function homeVisite(): BelongsTo
    {
        return $this->belongsTo(HomeVisite::class);
    }
}
