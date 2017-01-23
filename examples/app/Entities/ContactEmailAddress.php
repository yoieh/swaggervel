<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(definition="ContactEmailAddress", type="object")
 * @SWG\Property(property="email_address", type="string", example="florian.greinus@gmail.com ")
 * @SWG\Property(property="email_address_normalized", type="string", example="florian.greinus@gmail.com")
 * @SWG\Property(property="label", type="string", example="Privat")
 */
class ContactEmailAddress extends Model
{
    public $fillable = ['email_address', 'label'];
    public $visible = ['email_address', 'email_address_normalized', 'label'];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
