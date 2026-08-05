<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $table = 'fournisseurs';

    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'adresse',
        'ville',
        'code_postal',
        'pays',
        'notes',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /* Relations */

    public function commandes()
    {
        return $this->hasMany(CommandeAchat::class);
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'produit_fournisseur')
                    ->withPivot('prix_achat', 'delai_livraison', 'reference_fournisseur', 'quantite_minimum', 'prefere')
                    ->withTimestamps();
    }

    /* Helpers */

    public function getNomCompletAttribute()
    {
        return trim("{$this->nom}");
    }
}
