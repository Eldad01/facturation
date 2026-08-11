<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneFacture extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'produit_id',
        'quantite',
        'quantite_payee',
        'prix_unitaire',
        'total_ligne',
        'remise'
    ];

    protected $table = 'lignes_facture';

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function lignePaiements()
    {
        return $this->hasMany(LignePaiement::class);
    }

    public function getQuantiteRestanteAttribute()
    {
        return max(0, $this->quantite - $this->quantite_payee);
    }

    public function isFullyPaid(): bool
    {
        return $this->quantite_payee >= $this->quantite;
    }
}
