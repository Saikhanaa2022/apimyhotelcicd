<?php

namespace App\Traits;

trait HasTranslation
{
	/**
	 * Get the translation for the model.
	 *
	 * @return array
	 */
	public function getTranslateAttribute()
	{
        $translations = $this->translations();
        $locale = config('app.locale');
        $results = $translations->where('locale', $locale)->get();

        if(count($results) == 1) {
            return $results[0];
        } else {
            $translations = $this->translations();
            $result = $translations->where('locale', config('app.fallback_locale'))->get();
            return count($result) == 1 ? $result[0] : null;
        }

        return $result;
	}

    public function getTranslationsAttribute()
	{
        $translations = $this->translations()->get();
        return $translations;
	}
}
