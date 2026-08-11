<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'montant',
        'mode',
        'reference',
        'note',
        'date_paiement',
    ];

    protected $casts = [
        'date_paiement' => 'date',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function lignes()
    {
        return $this->hasMany(LignePaiement::class);
    }
}
