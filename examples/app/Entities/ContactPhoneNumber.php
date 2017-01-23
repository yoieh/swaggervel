<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(definition="ContactPhoneNumber", type="object")
 * @SWG\Property(property="phone_number", type="string", example="0522617587")
 * @SWG\Property(property="phone_number_normalized", type="string", example="+49522617587")
 * @SWG\Property(property="label", type="string", example="Privat")
 */
class ContactPhoneNumber extends Model
{
    public $fillable = ['phone_number', 'label'];
    public $visible = ['phone_number', 'phone_number_normalized', 'label'];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
