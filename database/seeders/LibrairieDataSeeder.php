<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Convertit les données de démonstration (commerce général) en données de librairie.
 *
 * Chaque produit existant (même id) devient un article de librairie AU MÊME PRIX
 * d'achat et de vente : factures, paiements, stocks, mouvements et commandes
 * restent donc cohérents sans aucun recalcul.
 *
 * Rejouable sans risque : mise à jour par id, dans une transaction.
 *   php artisan db:seed --class=LibrairieDataSeeder
 *
 * Les ISBN sont fictifs (préfixe 978-2-9xxxx) mais ont une clé de contrôle valide.
 */
class LibrairieDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->produits();
            $this->fournisseurs();
            $this->clientsEtablissements();
            $this->commandes();
            $this->parametres();
        });

        $this->command?->info('Données converties en librairie : 45 articles, 10 fournisseurs, 4 établissements clients.');
    }

    /** ISBN-13 fictif mais valide (clé de contrôle calculée). */
    private function isbn(int $n): string
    {
        $base = '97829' . str_pad((string) (60000 + $n * 37), 7, '0', STR_PAD_LEFT); // 12 chiffres
        $somme = 0;
        foreach (str_split($base) as $i => $chiffre) {
            $somme += (int) $chiffre * ($i % 2 === 0 ? 1 : 3);
        }
        $cle = (10 - $somme % 10) % 10;
        $isbn = $base . $cle;

        return substr($isbn, 0, 3) . '-' . substr($isbn, 3, 1) . '-' . substr($isbn, 4, 5) . '-' . substr($isbn, 9, 3) . '-' . substr($isbn, 12, 1);
    }

    private function produits(): void
    {
        // id => [nom, famille, catégorie, unité, description]  (prix inchangés)
        $L = 'Livres';
        $S = 'Scolaire';
        $P = 'Papeterie';

        $articles = [
            1  => ["L'Enfant noir - Camara Laye", $L, 'Roman africain', 'exemplaire', 'Roman autobiographique, classique de la littérature africaine. Poche.'],
            2  => ['Crayon à papier HB', $P, 'Écriture', 'unité', 'Crayon graphite HB avec gomme.'],
            3  => ['Une si longue lettre - Mariama Bâ', $L, 'Roman africain', 'exemplaire', 'Roman épistolaire, programme du secondaire.'],
            4  => ['Atlas géographique mondial', $L, 'Dictionnaires & référence', 'exemplaire', 'Atlas illustré, cartes physiques et politiques, édition récente.'],
            5  => ['Les Soleils des indépendances - Ahmadou Kourouma', $L, 'Roman africain', 'exemplaire', 'Roman, classique au programme du lycée.'],
            6  => ['Gomme blanche', $P, 'Écriture', 'unité', 'Gomme plastique sans PVC.'],
            7  => ['Cahier 300 pages grand format', $P, 'Cahiers & papier', 'unité', 'Cahier 24x32, grands carreaux, couverture polypropylène.'],
            8  => ['Stylo à bille bleu', $P, 'Écriture', 'unité', 'Stylo à bille pointe moyenne, encre bleue.'],
            9  => ['Cahier 100 pages petit format', $P, 'Cahiers & papier', 'unité', 'Cahier 17x22, grands carreaux.'],
            10 => ['Règle plate 30 cm', $P, 'Géométrie & calcul', 'unité', 'Règle transparente graduée.'],
            11 => ['Taille-crayon double', $P, 'Écriture', 'unité', 'Taille-crayon 2 trous avec réservoir.'],
            12 => ['Bloc-notes A5 ligné', $P, 'Cahiers & papier', 'unité', 'Bloc de 100 feuilles détachables.'],
            13 => ['Surligneurs fluo (lot de 4)', $P, 'Écriture', 'lot', 'Jaune, vert, rose, orange.'],
            14 => ['Coffret Bescherelle (3 volumes)', $L, 'Dictionnaires & référence', 'coffret', 'Conjugaison, grammaire et orthographe.'],
            15 => ['Mathématiques 6e - manuel', $S, 'Manuels du secondaire', 'exemplaire', 'Manuel de mathématiques, classe de 6e, programme national.'],
            16 => ['Dictionnaire Larousse de poche', $L, 'Dictionnaires & référence', 'exemplaire', 'Dictionnaire de la langue française, format poche.'],
            17 => ['Lecture CP - Mamadou et Bineta', $S, 'Manuels du primaire', 'exemplaire', "Méthode de lecture pour le cours préparatoire."],
            18 => ['Calculatrice scientifique', $P, 'Géométrie & calcul', 'unité', 'Calculatrice scientifique, recommandée collège et lycée.'],
            19 => ['Le Petit Larousse illustré', $L, 'Dictionnaires & référence', 'exemplaire', 'Dictionnaire encyclopédique illustré, grand format.'],
            20 => ['Français 6e - manuel', $S, 'Manuels du secondaire', 'exemplaire', 'Manuel de français, classe de 6e.'],
            21 => ["Histoire générale de l'Afrique (vol. 1)", $L, 'Histoire & société', 'exemplaire', 'Ouvrage de référence, histoire du continent africain.'],
            22 => ['Crayons de couleur (boîte de 12)', $P, 'Dessin', 'boîte', '12 couleurs assorties.'],
            23 => ['Ardoise + craies', $S, 'Fournitures du primaire', 'unité', 'Ardoise effaçable avec boîte de craies blanches.'],
            24 => ['Kit de géométrie complet', $P, 'Géométrie & calcul', 'kit', 'Compas, rapporteur, équerre et règle.'],
            25 => ['Album de coloriage enfant', $L, 'Jeunesse', 'exemplaire', 'Album de coloriage, 3-6 ans.'],
            26 => ['Contes et légendes du Burkina', $L, 'Jeunesse', 'exemplaire', 'Recueil de contes traditionnels illustrés.'],
            27 => ['Feuilles doubles grands carreaux (paquet de 50)', $P, 'Cahiers & papier', 'paquet', 'Copies doubles perforées, format A4.'],
            28 => ['Anglais Terminale - manuel', $S, 'Manuels du secondaire', 'exemplaire', "Manuel d'anglais, classe de Terminale."],
            29 => ['Chemises cartonnées (lot de 5)', $P, 'Classement', 'lot', 'Chemises A4, couleurs assorties.'],
            30 => ['Feutres de couleur (pochette de 12)', $P, 'Dessin', 'pochette', 'Feutres lavables, pointe moyenne.'],
            31 => ["Le monde s'effondre - Chinua Achebe", $L, 'Roman africain', 'exemplaire', 'Roman, traduction française.'],
            32 => ["Sous l'orage - Seydou Badian", $L, 'Roman africain', 'exemplaire', 'Roman, programme du collège.'],
            33 => ['Maïmouna - Abdoulaye Sadji', $L, 'Roman africain', 'exemplaire', 'Roman, classique de la littérature sénégalaise.'],
            34 => ['Cahier de dessin 32 pages', $P, 'Dessin', 'unité', 'Papier dessin 120 g, format 24x32.'],
            35 => ['Le Petit Prince - Antoine de Saint-Exupéry', $L, 'Jeunesse', 'exemplaire', 'Conte philosophique illustré par l\'auteur.'],
            36 => ['Encyclopédie junior (coffret 4 volumes)', $L, 'Dictionnaires & référence', 'coffret', 'Encyclopédie illustrée pour les 8-14 ans.'],
            37 => ['Mathématiques CM2 - manuel', $S, 'Manuels du primaire', 'exemplaire', 'Manuel de mathématiques, cours moyen 2e année.'],
            38 => ["L'Aventure ambiguë - Cheikh Hamidou Kane", $L, 'Roman africain', 'exemplaire', 'Roman, programme du lycée.'],
            39 => ['Bloc-notes A5 quadrillé', $P, 'Cahiers & papier', 'unité', 'Bloc de 100 feuilles, petits carreaux.'],
            40 => ['Agenda scolaire 2026-2027', $S, 'Fournitures du primaire', 'unité', 'Agenda une page par jour, de septembre à juillet.'],
            41 => ['Colle en bâton 21 g', $P, 'Classement', 'unité', 'Colle sans solvant, lavable.'],
            42 => ["Les Contes d'Amadou Koumba - Birago Diop", $L, 'Jeunesse', 'exemplaire', 'Recueil de contes, classique au programme.'],
            43 => ['Protège-cahiers (lot de 5)', $P, 'Classement', 'lot', 'Protège-cahiers 17x22 transparents colorés.'],
            44 => ['Dictionnaire bilingue français-anglais', $L, 'Dictionnaires & référence', 'exemplaire', 'Dictionnaire bilingue, 100 000 mots et expressions.'],
            45 => ['Sac à dos scolaire', $S, 'Fournitures du primaire', 'unité', 'Sac à dos renforcé, 2 compartiments.'],
        ];

        $pap = 0;
        $fou = 0;
        foreach ($articles as $id => [$nom, $famille, $categorie, $unite, $description]) {
            $estLivre = in_array($famille, [$L, $S], true) && $unite !== 'unité' && $unite !== 'kit';
            $sku = match (true) {
                $estLivre => $this->isbn($id),
                $famille === $S => sprintf('FOU-%04d', ++$fou),
                default => sprintf('PAP-%04d', ++$pap),
            };

            DB::table('produits')->where('id', $id)->update([
                'nom' => $nom,
                'famille' => $famille,
                'categorie' => $categorie,
                'unite' => $unite,
                'description' => $description,
                'sku' => $sku,
                'updated_at' => now(),
            ]);
        }
    }

    private function fournisseurs(): void
    {
        // Noms fictifs ; téléphone et ville conservés
        $fournisseurs = [
            1  => ['Diffusion Livres du Faso', 'Distributeur de romans et ouvrages de littérature africaine.'],
            2  => ['Éditions Savoir Sahel', 'Éditeur et distributeur de manuels scolaires (primaire et secondaire).'],
            3  => ['Papeterie Centrale de Ouaga', 'Grossiste en fournitures de bureau et papeterie.'],
            4  => ['Jeunesse & Contes Distribution', 'Livres jeunesse, albums et contes illustrés.'],
            5  => ['Nakoulma Fournitures Scolaires', 'Fournitures scolaires : cahiers, ardoises, sacs, agendas.'],
            6  => ['Comptoir du Livre Sahélien', 'Dictionnaires, encyclopédies et ouvrages de référence.'],
            7  => ['Faso Bureautique', 'Calculatrices, kits de géométrie et petit matériel.'],
            8  => ['Grossiste Papier du Nord', 'Rames, feuilles doubles, blocs-notes et classement.'],
            9  => ['Import Livres Afrique', 'Importation d\'ouvrages et de dictionnaires bilingues.'],
            10 => ['Distributions Zongo Papeterie', 'Stylos, crayons, feutres et articles de dessin.'],
        ];

        foreach ($fournisseurs as $id => [$nom, $notes]) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nom)));
            DB::table('fournisseurs')->where('id', $id)->update([
                'nom' => $nom,
                'email' => $slug . '@fournisseur.bf',
                'notes' => $notes,
                'updated_at' => now(),
            ]);
        }
    }

    private function clientsEtablissements(): void
    {
        // Les 4 clients ayant le plus de factures deviennent des établissements (achats groupés de manuels).
        // nom = type d'établissement (en capitales), prenom = nom propre ; l'ancien nom devient le contact référent.
        $etablissements = [
            10 => ['COMPLEXE SCOLAIRE', 'Les Étoiles', 'Directeur des études'],
            30 => ['LYCÉE PRIVÉ', 'Excellence', 'Censeur'],
            2  => ['ÉCOLE PRIMAIRE', 'Wend-Panga', 'Directrice'],
            3  => ['BIBLIOTHÈQUE MUNICIPALE', 'De Koudougou', 'Responsable des acquisitions'],
        ];

        $n = 0;
        foreach ($etablissements as $id => [$nom, $prenom, $fonction]) {
            $client = DB::table('clients')->where('id', $id)->first();
            if (!$client) {
                continue;
            }
            $n++;
            // Rejouable : on ne réécrit le contact que si le client n'a pas encore été converti
            $contact = $client->type_client === 'entreprise' && $client->contact_nom
                ? $client->contact_nom
                : trim($client->prenom . ' ' . $client->nom) . " ($fonction)";

            DB::table('clients')->where('id', $id)->update([
                'type_client' => 'entreprise',
                'nom' => $nom,
                'prenom' => $prenom,
                'contact_nom' => $contact,
                'ifu' => sprintf('0010%04dX', 1200 + $n * 7),
                'rccm' => sprintf('BF-OUA-2019-B-%05d', 1400 + $n * 13),
                'regime_imposition' => 'RSI',
                'updated_at' => now(),
            ]);
        }
    }

    private function commandes(): void
    {
        $notes = [
            'Commande de rentrée scolaire - livraison avant septembre.',
            'Réassort des romans au programme du lycée.',
            'Fournitures pour la commande groupée des écoles.',
            'Complément dictionnaires suite à rupture de stock.',
        ];

        $ids = DB::table('commandes_achat')->whereNotNull('notes')->orderBy('id')->pluck('id');
        foreach ($ids as $i => $id) {
            DB::table('commandes_achat')->where('id', $id)->update(['notes' => $notes[$i % count($notes)]]);
        }
    }

    private function parametres(): void
    {
        DB::table('settings')->orderBy('id')->limit(1)->update([
            'company_name' => 'Librairie Le Savoir',
            'footer_text' => 'Merci de votre visite - Bonne lecture !',
            'updated_at' => now(),
        ]);
    }
}
