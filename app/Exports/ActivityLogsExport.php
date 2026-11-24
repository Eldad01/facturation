<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivityLogsExport implements FromCollection, WithHeadings
{
    protected $filters;
    protected $periodInfo;
    protected $logs;

    public function __construct($logs = [], $periodInfo = '')
    {
        $this->logs = $logs;
        $this->periodInfo = $periodInfo;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        if ($this->logs instanceof \Illuminate\Database\Eloquent\Collection) {
            $logs = $this->logs;
        } else {
            // Fallback: build query from filters (legacy support)
            $filters = is_array($this->logs) ? $this->logs : [];
            $query = ActivityLog::with('user');

            if (!empty($filters['search'])) {
                $query->where('description', DB::getDriverName() === 'pgsql' ? 'ilike' : 'like','%' . $filters['search'] . '%');
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            if (!empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (!empty($filters['action'])) {
                $query->where('action', $filters['action']);
            }

            if (!empty($filters['user_role'])) {
                if ($filters['user_role'] === 'admin') {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', 'admin');
                    });
                } elseif ($filters['user_role'] === 'employe') {
                    $query->whereHas('user', function ($q) {
                        $q->where('role', 'employe');
                    });
                }
            }

            $logs = $query->latest()->get();
        }

        return $logs->map(function ($log) {
            return [
                'Date' => $log->created_at->format('d/m/Y H:i:s'),
                'Utilisateur' => $log->user->name ?? 'Système',
                'Role' => $log->user->role ?? '-',
                'Action' => $log->action,
                'Objet' => class_basename($log->model_type),
                'Description' => $log->description,
                'Impact financier' => $log->impact_financier ? number_format($log->impact_financier, 0, ',', ' ') : '-',
                'Gravité' => $log->niveau_gravite ?? 'info',
                'IP' => $log->ip_address ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Utilisateur',
            'Rôle',
            'Action',
            'Objet',
            'Description',
            'Impact financier',
            'Gravité',
            'Adresse IP',
        ];
    }

    public function getPeriodInfo()
    {
        return $this->periodInfo;
    }
}
