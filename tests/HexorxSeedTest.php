<?php

use AwkwardIdeas\HexorxCountriesBundler\Commands\SeedFromHexorxCountries;

class HexorxSeedTest extends PHPUnit\Framework\TestCase
{
    public function test_download_from_github(){
        $seeder = new seedFromHexorxCountries();
        $seeder->getHexorxFiles();
    }

    public function test_download_files(){
        $files = [];
        $base = file_get_contents('./hexorx_base_json.json');
        $countries = file_get_contents('./hexorx_countries_json.json');
        $subdivisions = file_get_contents('./hexorx_subdivisions_json.json');

        $items = json_decode($base);
        $items = array_merge($items, json_decode($countries));
        $items = array_merge($items, json_decode($subdivisions));

        foreach($items as $item){
            if($item->type === "file"){
                $files[] = $item;
            }
        }

        $seeder = new seedFromHexorxCountries();


        $seeder->downloadGithubFiles($files);
    }

    public function test_create_countries_json(){
        $seeder = new seedFromHexorxCountries();
        $seeder->createCountriesJSON();
    }

    public function test_create_json_files_from_yaml(){
        $seeder = new seedFromHexorxCountries();
        $seeder->convertYamlFilesToJSONFiles();
    }

    public function test_create_json_file_with_countries_and_their_districts(){
        $seeder = new seedFromHexorxCountries();
        $seeder->createJSONWithCountriesAndTheirDistricts();
    }

    public function test_culture(){
        //print_r(Locale::getDisplayScript(Locale::getDefault()));
        $formatter = new IntlDateFormatter('en-US',
            IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        print_r($formatter);
        //print_r(new CultureInfo('en-US'));
    }
}