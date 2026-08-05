<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = [
        'nom',
        'sku',
        'categorie',
        'famille',
        'unite',
        'description',
        'photo',
        'prix_achat',
        'prix_vente',
        'stock',
        'seuil_alerte'
    ];

    /* Relations */

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    /* Helpers */

    public function getStockActuelAttribute()
    {
        return $this->stock;
    }

    public function enAlerte(): bool
    {
        return $this->stock <= $this->seuil_alerte;
    }
}
