<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Services\ActivityLogger;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                // Nom et prénom sont deux colonnes séparées : on découpe la recherche en mots
                // pour que "GALLET Émile" (ou "Émile GALLET") trouve le client quel que soit l'ordre.
                $mots = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                $q->where(function ($qc) use ($mots) {
                    foreach ($mots as $mot) {
                        $qc->where(function ($w) use ($mot) {
                            $w->where('nom', 'like', "%$mot%")
                              ->orWhere('prenom', 'like', "%$mot%");
                        });
                    }
                })->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('nom')->orderBy('prenom')->paginate(10);

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $client->load('factures');
        return view('clients.show', compact('client'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ\'\- ]+$/'],
            'prenom' => ['required', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ\'\- ]+$/'],
            'telephone' => ['required', 'string', 'max:20', 'unique:clients,telephone', 'regex:/^\+?\d+$/'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clients,email'],
            'adresse' => 'nullable|string|max:255',
            'type_client' => ['required', 'in:particulier,entreprise'],
            'ifu' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'boite_postale' => 'nullable|string|max:100',
            'regime_imposition' => 'nullable|string|max:255',
            'contact_nom' => 'nullable|string|max:255',
        ]);

        $data = $request->only('nom', 'prenom', 'telephone', 'email', 'adresse', 'type_client', 'ifu', 'rccm', 'boite_postale', 'regime_imposition', 'contact_nom');
        $data['nom'] = strtoupper($data['nom']);
        $data['prenom'] = ucfirst(strtolower($data['prenom']));

        $client = Client::create($data);

        // Enregistrer l'activité
        ActivityLogger::created(
            $client,
            "Ajout du client {$client->nomComplet}"
        );

        return redirect()->route('clients.index')->with('success', 'Client ajouté avec succès !');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ\'\- ]+$/'],
            'prenom' => ['required', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ\'\- ]+$/'],
            'telephone' => ['required', 'string', 'max:20', 'unique:clients,telephone,' . $client->id, 'regex:/^\+?\d+$/'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clients,email,' . $client->id],
            'adresse' => 'nullable|string|max:255',
            'type_client' => ['required', 'in:particulier,entreprise'],
            'ifu' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'boite_postale' => 'nullable|string|max:100',
            'regime_imposition' => 'nullable|string|max:255',
            'contact_nom' => 'nullable|string|max:255',
        ]);

        $oldData = [
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'telephone' => $client->telephone,
            'email' => $client->email,
            'type_client' => $client->type_client,
        ];

        $data = $request->only('nom', 'prenom', 'telephone', 'email', 'adresse', 'type_client', 'ifu', 'rccm', 'boite_postale', 'regime_imposition', 'contact_nom');
        $data['nom'] = strtoupper($data['nom']);
        $data['prenom'] = ucfirst(strtolower($data['prenom']));

        $client->update($data);

        // Enregistrer l'activité
        ActivityLogger::updated(
            $client,
            "Modification du client {$client->nomComplet}",
            null,
            $oldData
        );

        return redirect()->route('clients.index')->with('success', 'Client modifié avec succès !');
    }

    public function destroy(Client $client)
    {
        // Only admin can delete clients
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('clients.index')->with('error', 'Vous n\'avez pas le droit de supprimer des clients.');
        }

        if ($client->factures()->exists()) {
            return redirect()->route('clients.index')
                ->with('error', 'Ce client a des factures associées et ne peut pas être supprimé.');
        }

        $clientName = $client->nomComplet;
        
        $client->delete();

        // Enregistrer l'activité
        ActivityLogger::deleted(
            $client,
            "Suppression du client {$clientName}"
        );

        return redirect()->route('clients.index')->with('success', 'Client supprimé !');
    }
}
