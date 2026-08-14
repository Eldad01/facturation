<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    protected $fillable = [
        'produit_id',
        'type',
        'quantite',
        'raison',
        'reference',
        'prix_unitaire',
    ];

    /* Relations */

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /* 🔥 SYNCHRONISATION AUTOMATIQUE DU STOCK */

    protected static function booted()
    {
        static::created(function ($mouvement) {

            $produit = $mouvement->produit;

            if (!$produit) {
                return;
            }

            if ($mouvement->type === 'entree') {
                $produit->increment('stock', $mouvement->quantite);
            }

            if ($mouvement->type === 'sortie') {
                $produit->decrement('stock', $mouvement->quantite);
            }
        });
    }
}
