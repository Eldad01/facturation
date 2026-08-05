<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceptionAchat extends Model
{
    protected $table = 'receptions_achat';

    protected $fillable = [
        'numero',
        'commande_achat_id',
        'date_reception',
        'notes',
    ];

    protected $casts = [
        'date_reception' => 'date',
    ];

    /* Relations */

    public function commande()
    {
        return $this->belongsTo(CommandeAchat::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneReceptionAchat::class);
    }

    /* Helpers */

    public function getQuantiteTotaleReçueAttribute()
    {
        return $this->lignes()->sum('quantite_reçue');
    }

    public function isProcessed(): bool
    {
        return $this->lignes->count() > 0;
    }
}
