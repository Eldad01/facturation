<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LignePaiement extends Model
{
    protected $fillable = [
        'paiement_id',
        'ligne_facture_id',
        'quantite',
        'montant',
    ];

    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }

    public function ligneFacture()
    {
        return $this->belongsTo(LigneFacture::class);
    }
}
