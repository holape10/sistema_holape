<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'description'
    ];

    public function users()
	{
	    return $this
	        ->belongsToMany('MasterSoft\User')
	        ->withTimestamps();
	}
}
