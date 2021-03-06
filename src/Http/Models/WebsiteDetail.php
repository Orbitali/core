<?php

namespace Orbitali\Http\Models;

use Orbitali\Http\Traits\Cacheable;
use Orbitali\Http\Traits\ExtendDetail;
use Orbitali\Http\Traits\ExtendExtra;
use Illuminate\Database\Eloquent\Model;

class WebsiteDetail extends Model
{
    use Cacheable, ExtendExtra, ExtendDetail;

    public $timestamps = false;
    protected $guarded = [];
    protected $table = "website_details";
    protected $touches = ["parent"];
    public static $withoutExtra = [
        "id",
        "website_id",
        "language",
        "country",
        "name",
        "slug",
    ];
    protected $casts = [
        "language" => "string",
        "country" => "string",
        "name" => "string",
    ];

    public function parent()
    {
        return $this->belongsTo(Website::class, "website_id");
    }

    public function url()
    {
        return $this->morphOne(Url::class, "model");
    }

    public function extras()
    {
        return $this->hasMany(WebsiteDetailExtra::class);
    }
}
