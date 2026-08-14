<?php

namespace App\Models;

use App\Models\Paiement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'type_document',   // pro-forma | recu
        'status',
        'numero_facture',
        'total',
        'montant_paye',
        'date_echeance',
        'date_paiement',
        'modifiable',
        'remise',
        'tva'
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_paiement' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneFacture::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function getBalanceAttribute()
    {
        return max(0, $this->total - $this->montant_paye);
    }

    public function isPaid(): bool
    {
        return $this->status === 'payee' || $this->balance <= 0;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partiellement_payee';
    }

    public function isOverdue(): bool
    {
        return $this->date_echeance && now()->gt($this->date_echeance) && !$this->isPaid();
    }

    public function isDefinitive()
    {
        return $this->type_document === 'recu';
    }

    public function getEtatAttribute(): string
    {
        if ($this->status === 'annule') return 'annulee';
        if ($this->type_document === 'pro-forma') return 'devis';
        if ($this->status === 'payee') return 'payee';
        if ($this->status === 'partiellement_payee') return 'partiellement_payee';
        return 'non_payee';
    }

    public function getEtatLabelAttribute(): string
    {
        return match($this->etat) {
            'devis' => 'Devis',
            'non_payee' => 'En attente de paiement',
            'partiellement_payee' => 'Partiellement payée',
            'payee' => 'Payée',
            'annulee' => 'Annulée',
        };
    }

    public function getEtatBadgeClassAttribute(): string
    {
        return match($this->etat) {
            'devis' => 'bg-warning-subtle text-warning',
            'non_payee' => 'bg-secondary-subtle text-secondary',
            'partiellement_payee' => 'bg-warning text-dark',
            'payee' => 'bg-success-subtle text-success',
            'annulee' => 'bg-secondary',
        };
    }

    public static function generateNumeroFor(string $type_document)
    {
        $prefix = $type_document === 'pro-forma' ? 'PF' : 'R';
        $base = $prefix . date('Ymd');

        $last = self::where('numero_facture', 'like', $base.'%')
            ->orderByDesc('numero_facture')
            ->first();

        if (!$last) {
            return $base.'001';
        }

        $lastNumber = (int) substr($last->numero_facture, -3);

        return $base . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}
