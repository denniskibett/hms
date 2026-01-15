<?php

namespace App\Helpers;

use PragmaRX\Countries\Package\Countries;

class CountriesHelper
{
    private static $countries = null;
    
    public static function getAllCountries()
    {
        if (self::$countries === null) {
            $countriesPackage = new Countries();
            $countries = $countriesPackage->all();
            
            self::$countries = $countries->map(function ($country) {
                return [
                    'name' => $country->name->common ?? '',
                    'official_name' => $country->name->official ?? '',
                    'cca2' => $country->cca2 ?? '',
                    'cca3' => $country->cca3 ?? '',
                    'currencies' => $country->currencies ?? [],
                    'languages' => $country->languages ?? [],
                    'timezones' => $country->timezones ?? [],
                ];
            })->sortBy('name')->values();
        }
        
        return self::$countries;
    }
    
    public static function getCountryList()
    {
        return self::getAllCountries()->pluck('name')->toArray();
    }
    
    public static function getCountryByCode($code)
    {
        $countries = self::getAllCountries();
        return $countries->firstWhere('cca2', $code) ?? 
               $countries->firstWhere('cca3', $code);
    }
    
    public static function getCountryName($code)
    {
        $country = self::getCountryByCode($code);
        return $country['name'] ?? '';
    }
}