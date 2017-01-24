<?php

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(definition="Contact", type="object")
 * @SWG\Property(property="id", type="integer", example="5")
 * @SWG\Property(property="user_id", type="integer", example="4")
 * @SWG\Property(property="name", type="string", example="Testi Tester")
 * @SWG\Property(property="is_favorite", type="boolean", example=true)
 * @SWG\Property(property="email_addresses", type="array",
 *     @SWG\Items(type="object", ref="#/definitions/ContactEmailAddress")
 * ),
 * @SWG\Property(property="phone_numbers", type="array",
 *     @SWG\Items(type="object", ref="#/definitions/ContactPhoneNumber")
 * ),
 */
class Contact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_favorite'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'name',  'is_favorite', 'user_id', 'emailAddresses', 'phoneNumbers',
    ];

    public $casts = [
        'is_favorite' => 'boolean'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function phoneNumbers()
    {
        return $this->hasMany(ContactPhoneNumber::class);
    }

    public function emailAddresses()
    {
        return $this->hasMany(ContactEmailAddress::class);
    }
}
