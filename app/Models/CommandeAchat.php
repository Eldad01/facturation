<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeAchat extends Model
{
    protected $table = 'commandes_achat';

    protected $fillable = [
        'numero',
        'fournisseur_id',
        'statut',
        'date_commande',
        'date_reception_prevue',
        'date_reception',
        'montant_total',
        'notes',
    ];

    protected $casts = [
        'date_commande' => 'date',
        'date_reception_prevue' => 'date',
        'date_reception' => 'date',
    ];

    /* Relations */

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneCommandeAchat::class);
    }

    public function receptions()
    {
        return $this->hasMany(ReceptionAchat::class);
    }

    /* Helpers */

    public function getMontantTotalAttribute()
    {
        return $this->lignes()->sum('montant');
    }

    public function getQuantiteTotalAttribute()
    {
        return $this->lignes()->sum('quantite');
    }

    public function getQuantiteReçueAttribute()
    {
        return $this->lignes()->sum('quantite_reçue');
    }

    public function isEditable(): bool
    {
        return in_array($this->statut, ['brouillon', 'confirmee']);
    }

    public function canConfirm(): bool
    {
        return $this->statut === 'brouillon' && $this->lignes->count() > 0;
    }

    public function canCancel(): bool
    {
        return !in_array($this->statut, ['reçue', 'annulee']);
    }
}
