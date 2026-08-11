<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActivityLogsExport;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Filtres
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $userId = $request->input('user_id');
        $action = $request->input('action');
        $hasFinancialImpact = $request->input('financial_impact');
        $gravity = $request->input('gravity');
        $objectType = $request->input('object_type');

        // Appliquer les filtres de dates prédéfinis
        if ($request->filled('date_preset')) {
            $datePreset = $request->input('date_preset');
            switch ($datePreset) {
                case 'today':
                    $dateFrom = now()->toDateString();
                    $dateTo = now()->toDateString();
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo = now()->toDateString();
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->toDateString();
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->toDateString();
                    break;
            }
        }

        $logs = ActivityLog::with('user')
            ->when($search, function ($query, $search) {
                $query->where('description', 'like', "%$search%");
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($userId, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($hasFinancialImpact === 'yes', function ($query) {
                $query->whereNotNull('impact_financier')->where('impact_financier', '>', 0);
            })
            ->when($gravity, function ($query, $gravity) {
                $query->where('niveau_gravite', $gravity);
            })
            ->when($objectType, function ($query, $objectType) {
                $query->where('model_type', 'like', "%$objectType");
            })
            ->latest()
            ->paginate(15);

        // Données pour les filtres
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::distinct()->pluck('action')->filter()->values();
        $gravities = ['info', 'warning', 'error', 'critical'];
        $objectTypes = ActivityLog::distinct()->pluck('model_type')->filter()->values();

        // Statistiques rapides
        $stats = $this->getStats();

        return view('historique.index', compact(
            'logs', 'search', 'dateFrom', 'dateTo', 'userId', 
            'action', 'hasFinancialImpact', 'gravity', 'objectType',
            'users', 'actions', 'gravities', 'objectTypes', 'stats'
        ));
    }

    /**
     * Statistiques rapides
     */
    private function getStats()
    {
        $today = now()->toDateString();
        
        return [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', $today)->count(),
            'with_financial_impact' => ActivityLog::whereNotNull('impact_financier')
                ->where('impact_financier', '>', 0)->count(),
            'total_impact' => ActivityLog::sum('impact_financier') ?? 0,
            'by_action' => ActivityLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')->pluck('count', 'action')->toArray(),
            'by_type' => ActivityLog::select('model_type', DB::raw('count(*) as count'))
                ->groupBy('model_type')->pluck('count', 'model_type')->toArray(),
        ];
    }

    /**
     * Obtenir les données pour les graphiques
     */
    public function chartData(Request $request)
    {
        $days = $request->input('days', 30);
        
        $data = ActivityLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
        ->where('created_at', '>=', now()->subDays($days))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json($data);
    }

    /**
     * Exporter en Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->all();
        
        // Préparer les infos de période pour l'en-tête
        $periodInfo = $this->getPeriodInfo($request);
        
        // Filtrer par rôle utilisateur si demandé
        $logsQuery = ActivityLog::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('description', 'like', "%$search%");
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->user_role, function ($query, $role) {
                if ($role === 'admin') {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', 'admin');
                    });
                } elseif ($role === 'employe') {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', 'employe');
                    });
                }
            })
            ->latest();
        
        $logs = $logsQuery->get();
        
        return Excel::download(new ActivityLogsExport($logs, $periodInfo), 'historique_actions_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Exporter en PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->all();
        $perPage = $request->input('per_page', 50);
        
        // Préparer les infos de période pour l'en-tête
        $periodInfo = $this->getPeriodInfo($request);
        
        // Filtrer par rôle utilisateur si demandé
        $logsQuery = ActivityLog::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('description', 'like', "%$search%");
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->user_role, function ($query, $role) {
                if ($role === 'admin') {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', 'admin');
                    });
                } elseif ($role === 'employe') {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', 'employe');
                    });
                }
            })
            ->latest();
        
        $logs = $logsQuery->limit($perPage)->get();

        $pdf = \PDF::loadView('historique.export_pdf', compact('logs', 'periodInfo'));
        return $pdf->download('historique_actions_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Préparer les informations de période pour les exports
     */
    private function getPeriodInfo(Request $request)
    {
        $periodText = '';
        
        if ($request->date_from && $request->date_to) {
            $periodText = 'Du ' . \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') 
                        . ' au ' . \Carbon\Carbon::parse($request->date_to)->format('d/m/Y');
        } elseif ($request->date_from) {
            $periodText = 'À partir du ' . \Carbon\Carbon::parse($request->date_from)->format('d/m/Y');
        } elseif ($request->date_to) {
            $periodText = 'Jusqu\'au ' . \Carbon\Carbon::parse($request->date_to)->format('d/m/Y');
        } else {
            $periodText = 'Toutes les périodes';
        }
        
        // Ajouter le type d'utilisateur
        $roleText = '';
        if ($request->user_role === 'admin') {
            $roleText = ' - Administrateurs uniquement';
        } elseif ($request->user_role === 'employe') {
            $roleText = ' - Employés uniquement';
        }
        
        return $periodText . $roleText;
    }

    /**
     * Nettoyer les anciens logs (admin uniquement)
     */
    public function cleanup(Request $request)
    {
        // Vérifier que c'est un admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Accès interdit');
        }

        // Validation basée sur le type de nettoyage choisi
        $type = $request->input('cleanup_type', 'days');

        if ($type === 'days') {
            // Suppression par nombre de jours
            $request->validate([
                'days' => 'required|integer|min:1|max:365',
            ], [
                'days.required' => 'Le nombre de jours est requis.',
                'days.integer' => 'Le nombre de jours doit être un entier.',
                'days.min' => 'Le nombre de jours doit être au moins 1.',
                'days.max' => 'Le nombre de jours ne peut pas dépasser 365.',
            ]);

            $days = $request->input('days');
            $dateLimit = now()->subDays($days);

            $count = ActivityLog::where('created_at', '<', $dateLimit)->count();

            if ($count > 0) {
                ActivityLog::where('created_at', '<', $dateLimit)->delete();
                
                return redirect()->route('historique.index')
                    ->with('success', "$count log(s) supprimé(s) avec succès.");
            }

            return redirect()->route('historique.index')
                ->with('info', 'Aucun log à supprimer pour cette période.');

        } elseif ($type === 'date_range') {
            // Suppression par période (date début - date fin)
            $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
            ], [
                'date_from.required' => 'La date de début est requise.',
                'date_to.required' => 'La date de fin est requise.',
                'date_to.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            ]);

            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $count = ActivityLog::whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->count();

            if ($count > 0) {
                ActivityLog::whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->delete();
                
                return redirect()->route('historique.index')
                    ->with('success', "$count log(s) supprimé(s) avec succès.");
            }

            return redirect()->route('historique.index')
                ->with('info', 'Aucun log à supprimer pour cette période.');
        }

        return redirect()->route('historique.index')
            ->with('error', 'Type de nettoyage invalide.');
    }

    /**
     * Obtenir les détails d'un log (pour la modale)
     */
    public function details(ActivityLog $activityLog)
    {
        return response()->json([
            'id' => $activityLog->id,
            'user' => $activityLog->user->name ?? 'Système',
            'action' => $activityLog->action,
            'object' => class_basename($activityLog->model_type),
            'description' => $activityLog->description,
            'impact_financier' => $activityLog->impact_financier,
            'gravite' => $activityLog->niveau_gravite,
            'ip' => $activityLog->ip_address,
            'created_at' => $activityLog->created_at->format('d/m/Y H:i:s'),
            'old_data' => $activityLog->old_data,
            'new_data' => $activityLog->new_data,
        ]);
    }
}
