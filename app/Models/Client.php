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
        'type_client',
        'ifu',
        'rccm',
        'boite_postale',
        'regime_imposition',
        'contact_nom',
    ];

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    // Nom complet formaté
    public function getNomCompletAttribute()
    {
        return mb_strtoupper((string) $this->nom, 'UTF-8') . ' ' . mb_convert_case((string) $this->prenom, MB_CASE_TITLE, 'UTF-8');
    }

    public function isEntreprise(): bool
    {
        return $this->type_client === 'entreprise';
    }
}
