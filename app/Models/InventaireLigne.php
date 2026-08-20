<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventaireLigne extends Model
{
    protected $table = 'inventaire_lignes';

    protected $fillable = [
        'inventaire_id',
        'produit_id',
        'stock_theorique',
        'stock_reel',
    ];

    public function inventaire()
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function getEcartAttribute()
    {
        return $this->stock_reel === null ? null : $this->stock_reel - $this->stock_theorique;
    }

    /**
     * Valeur monétaire de l'écart : écart (quantité) x CUMP d'achat du produit.
     */
    public function getEcartValoriseAttribute()
    {
        if ($this->ecart === null) {
            return null;
        }

        return $this->ecart * ($this->produit->prix_achat ?? 0);
    }
}
