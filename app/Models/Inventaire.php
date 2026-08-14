<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    protected $fillable = [
        'reference',
        'statut',
        'user_id_creation',
        'user_id_validation',
        'date_validation',
        'notes',
    ];

    protected $casts = [
        'date_validation' => 'datetime',
    ];

    public function lignes()
    {
        return $this->hasMany(InventaireLigne::class);
    }

    public function userCreation()
    {
        return $this->belongsTo(User::class, 'user_id_creation');
    }

    public function userValidation()
    {
        return $this->belongsTo(User::class, 'user_id_validation');
    }

    public function isValidee(): bool
    {
        return $this->statut === 'validee';
    }
}
