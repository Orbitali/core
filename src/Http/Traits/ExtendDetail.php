<?php

namespace Orbitali\Http\Traits;

use Orbitali\Foundations\Model;

trait ExtendDetail
{
    public function setSlugAttribute($value)
    {
        /** @var Model $url */
        $url = $this->url()->firstOrNew(['website_id' => orbitali('website')->id, 'type' => 'original']);

        $url->url = $value;
        if ($url->isDirty('url')) {
            $url->redirects()->updateOrCreate(
                [
                    'website_id' => $url->getOriginal('website_id'),
                    'type' => 'redirect',
                    'url' => $url->getOriginal('url')
                ]
            );
        }
        $url->save();

    }

    public function getSlugAttribute()
    {
        return $this->url;
    }
}
