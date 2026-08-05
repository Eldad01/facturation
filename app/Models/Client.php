<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
    ];

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    // Nom complet formaté
    public function getNomCompletAttribute()
    {
        return strtoupper($this->nom) . ' ' . ucfirst(strtolower($this->prenom));
    }
}
