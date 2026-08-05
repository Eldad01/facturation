<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneCommandeAchat extends Model
{
    protected $table = 'lignes_commande_achat';

    protected $fillable = [
        'commande_achat_id',
        'produit_id',
        'quantite',
        'quantite_reçue',
        'prix_unitaire',
        'montant',
    ];

    /* Relations */

    public function commande()
    {
        return $this->belongsTo(CommandeAchat::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function receptionsLignes()
    {
        return $this->hasMany(LigneReceptionAchat::class);
    }

    /* Helpers */

    public function getMontantAttribute()
    {
        return $this->quantite * $this->prix_unitaire;
    }

    public function getQuantiteManquanteAttribute()
    {
        return $this->quantite - $this->quantite_reçue;
    }

    public function isFullyReceived(): bool
    {
        return $this->quantite_reçue >= $this->quantite;
    }
}
