<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name','logo','address','phone',
        'email','ifu','devise','footer_text',
        'rccm','country','national_motto','tva_default',
        'boite_postale','regime_imposition','contact_nom','contact_telephone',
        'banque_nom','banque_numero_compte','banque_autres_comptes',
    ];
}
