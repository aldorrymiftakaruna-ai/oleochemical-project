<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmEquipment extends Model
{
    protected $table = 'cm_equipment';

    protected $fillable = [
        'equipment_tag',
        'pt_location',
        'plant',
        'tipe_lubrikasi',
    ];

    /** @return HasMany<CmReading> */
    public function readings(): HasMany
    {
        return $this->hasMany(CmReading::class, 'cm_equipment_id');
    }

    /** @return HasMany<CmFinding> */
    public function findings(): HasMany
    {
        return $this->hasMany(CmFinding::class, 'cm_equipment_id');
    }

    /** @return HasMany<CmMonthlyTracking> */
    public function monthlyTrackings(): HasMany
    {
        return $this->hasMany(CmMonthlyTracking::class, 'cm_equipment_id');
    }

    /**
     * Relasi ke Asset (Asset Management) — cocokkan equipment_tag dengan
     * equipment_no atau tech_ident_no di tabel assets.
     * Null jika tidak ada asset yang cocok.
     *
     * @return BelongsTo<Asset, CmEquipment>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'equipment_tag', 'equipment_no');
    }

    /**
     * Scope untuk filter berdasarkan PT.
     */
    public function scopeByPt($query, $pt)
    {
        return $query->where('pt_location', $pt);
    }

    /**
     * Scope untuk filter berdasarkan plant.
     */
    public function scopeByPlant($query, $plant)
    {
        return $query->where('plant', $plant);
    }
}
