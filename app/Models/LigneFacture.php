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
}
