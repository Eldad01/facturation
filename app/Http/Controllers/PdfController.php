<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Inventaire;
use App\Models\Setting;
use PDF;

class PdfController extends Controller
{
    public function generate(Facture $facture)
    {
        $facture->load([
            'client',
            'lignes.produit',
            'paiements',
        ]);

        $settings = Setting::first();

        $titre = $facture->type_document === 'recu'
            ? 'FACTURE'
            : 'PRO-FORMA';

        $pdf = PDF::loadView('pdf.facture', [
            'facture'  => $facture,
            'settings' => $settings,
            'titre'    => $titre
        ]);

        return $pdf->stream($titre . '-' . $facture->numero_facture . '.pdf');
    }

    public function generateInventaire(Inventaire $inventaire)
    {
        $inventaire->load([
            'lignes.produit',
            'userCreation',
            'userValidation',
        ]);

        $settings = Setting::first();

        $pdf = PDF::loadView('pdf.inventaire', [
            'inventaire' => $inventaire,
            'settings'   => $settings,
        ]);

        return $pdf->stream('INVENTAIRE-' . $inventaire->reference . '.pdf');
    }
}
