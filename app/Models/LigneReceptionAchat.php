<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneReceptionAchat extends Model
{
    protected $table = 'lignes_reception_achat';

    protected $fillable = [
        'reception_achat_id',
        'ligne_commande_achat_id',
        'quantite_reçue',
    ];

    /* Relations */

    public function reception()
    {
        return $this->belongsTo(ReceptionAchat::class, 'reception_achat_id');
    }

    public function ligneCommande()
    {
        return $this->belongsTo(LigneCommandeAchat::class, 'ligne_commande_achat_id');
    }
}
