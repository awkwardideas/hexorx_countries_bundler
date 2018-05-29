<?php namespace AwkwardIdeas\HexorxCountriesBundler\Commands;

use Illuminate\Console\Command;
use AwkwardIdeas\HexorxCountriesBundler\Models\Country as CountryHelper;
use Symfony\Component\Yaml\Yaml;
use Exception as Exception;


class SeedFromHexorxCountries extends Command
{
    protected $hexorx_countries_url = "https://api.github.com/repos/hexorx/countries";
    protected $path_to_hexorx_data =__DIR__.'/../../data/hexorxCountries';
    protected $request_options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Seed-Hexorx-Countries-PHP'
            ]
        ]
    ];

    function getHexorxFiles(){

        $mainURL = $this->hexorx_countries_url . "/contents/lib/countries/data";
        $folderItems = $this->readFolder($mainURL);
        $files = $this->processFolder($folderItems);
        $this->downloadGithubFiles($files);
    }
    private function readFolder($folderPath){
        $context = stream_context_create($this->request_options);
        $json = file_get_contents($folderPath, false, $context);
        return json_decode($json);
    }

    private function processFolder($folderItems){
        $files = [];
        foreach($folderItems as $folderItem){
            if($folderItem->type == "dir"){
                $subFolderItems = $this->readFolder($folderItem->url);
                $subFolderFiles = $this->processFolder($subFolderItems);
                $files = array_merge($files, $subFolderFiles);
            }else if($folderItem->type == "file"){
                $files[] = $folderItem;
            }
        }
        return $files;
    }

    function downloadGithubFiles($files){
        if(!file_exists($this->path_to_hexorx_data)){
          if(!@mkdir($this->path_to_hexorx_data, 0700, true) && !is_dir($this->path_to_hexorx_data)){
              throw new Exception("Directory Creation Failed");
          }
        }

        foreach($files as $file){
            $this->downloadGithubFile($file, $this->path_to_hexorx_data);
        }

    }
    private function downloadGithubFile($file, $destinationRoot){
        print "Downloading: " . $file->download_url;
        $context = stream_context_create($this->request_options);
        $source = fopen($file->download_url, 'r', false, $context);
        $stripString = '/lib\/countries\/data\//';
        $adjustedPath = preg_replace($stripString, '/', $file->path);
        $destinationPath = $destinationRoot.$adjustedPath;
        if(!file_exists(dirname($destinationPath))){
            if(!@mkdir(dirname($destinationPath), 0700, true) && !is_dir(dirname($destinationPath))){
                throw new Exception("Directory Creation Failed");
            }
        }

        $destination = fopen($destinationPath, 'wa+');

        $fileBytes = stream_copy_to_stream($source, $destination);
        print "Downloaded $fileBytes of $file->size bytes";
        if($fileBytes != $file->size){
            throw new Exception("File size mismatch");
        }
    }

    function convertYamlFilesToJSONFiles(){
        $yaml_files = $this->getDirectoryFilesByExtension($this->path_to_hexorx_data, "yaml");
        foreach($yaml_files as $yaml_file){
            $this->convertYamlFileToJSONFile($yaml_file);
        }
    }

    private function convertYamlFileToJSONFile($file){
        $fileData = Yaml::parse(file_get_contents($file));

        $destinationPath = preg_replace('/\.yaml$/i', ".json", $file);
        print "converting $file to $destinationPath" . PHP_EOL;
        $destination = fopen($destinationPath, 'wa+');
        fwrite($destination, json_encode($fileData));
        fclose($destination);
    }

    function getDirectoryFilesByExtension($directory, $extension){
        $result = array();

        $cdir = scandir($directory);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value,array(".","..")))
            {
                if (is_dir($directory . DIRECTORY_SEPARATOR . $value))
                {
                    $result = array_merge($result, $this->getDirectoryFilesByExtension($directory . DIRECTORY_SEPARATOR . $value, $extension));
                }
                else if(preg_match('/^.*\.('.$extension.')$/i', $value))
                {
                    $result[] = $directory . DIRECTORY_SEPARATOR . $value;
                }
            }
        }

        return $result;
    }

    function createCountriesAllJSON(){
        $countryFilesPath = $this->path_to_hexorx_data . "/countries/";
        $files = $this->getDirectoryFilesByExtension($countryFilesPath, 'yaml');

        $countryData = [];
        foreach($files as $file){
            $countryData[] = Yaml::parse(file_get_contents($file));
        }
        $destinationPath = $countryFilesPath."all.json";

        $destination = fopen($destinationPath, 'wa+');
        fwrite($destination, json_encode($countryData));
        fclose($destination);
    }

    function createJSONWithCountriesAndTheirDistricts(){
        $countryFilesPath = $this->path_to_hexorx_data . "/countries";
        $subdivisionFilesPath = $this->path_to_hexorx_data . "/subdivisions";

        $countryFiles= $this->getDirectoryFilesByExtension($countryFilesPath, 'yaml');
        //$subdivisionFiles= $this->getDirectoryFilesByExtension($subdivisionFilesPath, 'yaml');

        $countries = [];
        foreach($countryFiles as $countryFile){
            $country = Yaml::parse(file_get_contents($countryFile));
            $country[array_keys($country)[0]]["abbreviation"] = array_keys($country)[0];
            $country= $country[array_keys($country)[0]];
            if(file_exists(str_replace($countryFilesPath,$subdivisionFilesPath,$countryFile))){
                $country["subdivisions"] = Yaml::parse(file_get_contents(str_replace($countryFilesPath,$subdivisionFilesPath,$countryFile)));
            }else{
                print "MISSING: " .str_replace($countryFilesPath,$subdivisionFilesPath,$countryFile) . " does not exist" . PHP_EOL;
            }

            $country = new CountryHelper($country);
            $countries[] = $country;
        }

        $simpleArrayData = [];
        foreach($countries as $country){
            $simpleArrayData[] = $country->toSimpleArray();
        }

        $destinationPath = $this->path_to_hexorx_data."/all.json";

        $destination = fopen($destinationPath, 'wa+');
        fwrite($destination, json_encode($countries));
        fclose($destination);
    }
}