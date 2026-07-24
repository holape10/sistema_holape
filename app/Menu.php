<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    // Relación para obtener los submenús
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id')->orderBy('order', 'asc');
    }

    // Relación para obtener solo los menús principales (padres)
    public static function menusPrincipales()
    {
        return self::whereNull('parent_id')
                   ->orderBy('order', 'asc')
                   ->get();
    }
}
