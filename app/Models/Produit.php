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
        'seuil_alerte',
        'dernier_stock_reel',
        'date_dernier_inventaire',
    ];

    protected $casts = [
        'date_dernier_inventaire' => 'datetime',
    ];

    /* Relations */

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function inventaireLignes()
    {
        return $this->hasMany(InventaireLigne::class);
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
