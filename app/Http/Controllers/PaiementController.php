<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Paiement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    public function store(Request $request, Facture $facture)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode' => 'required|string|in:especes,carte,virement,mobile_money,autre',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'date_paiement' => 'nullable|date',
        ]);

        $montant = (float) $request->montant;
        $balance = max(0, $facture->total - $facture->montant_paye);

        if ($montant > $balance) {
            return back()->withErrors(['montant' => 'Le montant ne peut pas dépasser le solde restant (' . number_format($balance, 2, ',', ' ') . ').']);
        }

        DB::transaction(function () use ($request, $facture, $montant) {
            Paiement::create([
                'facture_id' => $facture->id,
                'montant' => $montant,
                'mode' => $request->mode,
                'reference' => $request->reference,
                'note' => $request->note,
                'date_paiement' => $request->date_paiement ?: now()->format('Y-m-d'),
            ]);

            $facture->montant_paye = $facture->montant_paye + $montant;
            $facture->date_paiement = $facture->montant_paye >= $facture->total ? now()->format('Y-m-d') : $facture->date_paiement;
            $facture->status = $facture->montant_paye >= $facture->total ? 'payee' : 'partiellement_payee';
            $facture->save();

            ActivityLogger::created(
                $facture,
                'Paiement de ' . number_format($montant, 0, ',', ' ') . ' CFA pour ' . $facture->numero_facture,
                $montant
            );
        });

        return back()->with('success', 'Paiement enregistré avec succès.');
    }
}
