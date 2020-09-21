<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FacebookCatalogProducts extends Model
{
    protected $table = 'facebook_catalog_products';

    public function create($data)
    {
        return $this->fill($data)
            ->saveOrFail();
    }
}
