<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    /**
     * Afficher la page de paramètres
     */
    public function edit()
    {
        $settings = Setting::first();
        $users = User::orderByDesc('created_at')->get();
        return view('settings.edit', compact('settings', 'users'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email|max:255',
            'ifu' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'national_motto' => 'nullable|string|max:255',
            'tva_default' => 'nullable|numeric|min:0|max:100',
            'boite_postale' => 'nullable|string|max:100',
            'regime_imposition' => 'nullable|string|max:255',
            'contact_nom' => 'nullable|string|max:255',
            'contact_telephone' => 'nullable|string|max:50',
            'banque_nom' => 'nullable|string|max:255',
            'banque_numero_compte' => 'nullable|string|max:100',
            'banque_autres_comptes' => 'nullable|string|max:1000',
        ]);

        $settings = Setting::first() ?? new Setting;
        
        $oldData = [
            'company_name' => $settings->company_name,
            'ifu' => $settings->ifu,
            'rccm' => $settings->rccm,
        ];

        $settings->company_name = $request->company_name;
        $settings->phone = $request->phone;
        $settings->address = $request->address;
        $settings->email = $request->email;
        $settings->ifu = $request->ifu;
        $settings->rccm = $request->rccm;
        $settings->country = $request->country;
        $settings->national_motto = $request->national_motto;
        $settings->tva_default = $request->tva_default ?? 0;
        $settings->boite_postale = $request->boite_postale;
        $settings->regime_imposition = $request->regime_imposition;
        $settings->contact_nom = $request->contact_nom;
        $settings->contact_telephone = $request->contact_telephone;
        $settings->banque_nom = $request->banque_nom;
        $settings->banque_numero_compte = $request->banque_numero_compte;
        $settings->banque_autres_comptes = $request->banque_autres_comptes;

        // Upload du logo - securisation stricte
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($settings->logo) {
                $oldPath = public_path('logos/' . $settings->logo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Validation stricte du fichier
            $file = $request->file('logo');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Verifier l'extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                return back()->withErrors(['logo' => 'Format non autorise. Formats acceptes: jpg, jpeg, png, gif, webp']);
            }
            
            // Verifier le type MIME reel
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return back()->withErrors(['logo' => 'Le fichier doit etre une image valide']);
            }
            
            // Generer un nom de fichier securise (sans extension dans le nom)
            $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            
            // Deplacer le fichier
            $file->move(public_path('logos'), $filename);
            $settings->logo = $filename;
        }

        $settings->save();

        Cache::forget('app_settings');

        // Enregistrer l'activité
        ActivityLogger::updated(
            $settings,
            "Mise à jour des paramètres de l'entreprise: {$request->company_name}",
            null,
            $oldData
        );

        return redirect()->route('settings.edit', ['tab' => 'company'])->with('success', 'Paramètres mis à jour avec succès !');
    }

    /**
     * reinitialiser les paramètre 
     */
    public function reset()
    {
        $settings = Setting::first();

        if ($settings) {
            // Supprimer le logo dans public/logos/
            if ($settings->logo) {
                $logoPath = public_path('logos/' . $settings->logo);
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }

            $settingsName = $settings->company_name;
            $settings->delete();
            
            // Enregistrer l'activité
            ActivityLogger::deleted(
                $settings,
                "Réinitialisation des paramètres de l'entreprise: {$settingsName}"
            );
        }

        Cache::forget('app_settings');

        return redirect()->route('settings.edit', ['tab' => 'company'])->with('success', 'Paramètres réinitialisés avec succès.');
    }

    /**
     * Ajouter un nouvel utilisateur
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role' => 'required|in:admin,employe',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        // Enregistrer l'activité
        ActivityLogger::created(
            $user,
            "Création de l'utilisateur {$user->name} ( {$user->email} ) - Rôle: {$user->role}"
        );

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', 'Utilisateur créé avec succès !');
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,employe',
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Enregistrer l'activité
        ActivityLogger::updated(
            $user,
            "Modification de l'utilisateur {$user->name} ( {$user->email} )",
            null,
            $oldData
        );

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', 'Utilisateur mis à jour avec succès !');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroyUser(User $user)
    {
        // Empêcher la suppression de soi-même
        if ($user->id === auth()->id()) {
            return redirect()->route('settings.edit', ['tab' => 'users'])->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $userName = $user->name;
        $userEmail = $user->email;
        
        $user->delete();

        // Enregistrer l'activité
        ActivityLogger::deleted(
            $user,
            "Suppression de l'utilisateur {$userName} ( {$userEmail} )"
        );

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', 'Utilisateur supprimé avec succès !');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('settings.edit', ['tab' => 'users'])->with('error', 'Vous ne pouvez pas modifier votre propre statut.');
        }

        $oldStatus = $user->is_active;
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activé' : 'désactivé';
        
        // Enregistrer l'activité
        ActivityLogger::updated(
            $user,
            "{$status} l'utilisateur {$user->name}",
            null,
            ['is_active' => $oldStatus]
        );

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', "Utilisateur $status avec succès !");
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Enregistrer l'activité
        ActivityLogger::updated(
            $user,
            "Réinitialisation du mot de passe de l'utilisateur {$user->name}",
            null,
            ['password' => '***']
        );

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', 'Mot de passe réinitialisé avec succès pour ' . $user->name . ' !');
    }
}
