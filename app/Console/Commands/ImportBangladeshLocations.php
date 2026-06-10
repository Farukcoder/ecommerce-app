<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;

#[Signature('db:import-locations')]
#[Description('Import/update all Bangladesh divisions, districts, and upazilas from API automatically')]
class ImportBangladeshLocations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Bangladesh Locations Importer ---');

        // 1. Import Divisions
        $this->info('Fetching divisions data...');
        $divisionsResponse = Http::get('https://raw.githubusercontent.com/ifahimreza/bangladesh-geojson/master/src/data/bd-divisions.json');

        if ($divisionsResponse->failed()) {
            $this->error('Failed to fetch divisions data from API.');
            return self::FAILURE;
        }

        $divisions = $divisionsResponse->json('divisions') ?? [];
        $this->info('Importing/updating divisions in database...');
        
        $bar = $this->output->createProgressBar(count($divisions));
        $bar->start();

        DB::transaction(function () use ($divisions, $bar) {
            foreach ($divisions as $division) {
                Division::updateOrCreate(
                    ['id' => $division['id']],
                    [
                        'name' => $division['name'],
                        'bn_name' => $division['bn_name'],
                        'lat' => $division['lat'] ?? null,
                        'long' => $division['long'] ?? null,
                    ]
                );
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Divisions imported successfully.');

        // 2. Import Districts
        $this->info('Fetching districts data...');
        $districtsResponse = Http::get('https://raw.githubusercontent.com/ifahimreza/bangladesh-geojson/master/src/data/bd-districts.json');

        if ($districtsResponse->failed()) {
            $this->error('Failed to fetch districts data from API.');
            return self::FAILURE;
        }

        $districts = $districtsResponse->json('districts') ?? [];
        $this->info('Importing/updating districts in database...');
        
        $bar = $this->output->createProgressBar(count($districts));
        $bar->start();

        DB::transaction(function () use ($districts, $bar) {
            foreach ($districts as $district) {
                District::updateOrCreate(
                    ['id' => $district['id']],
                    [
                        'division_id' => $district['division_id'],
                        'name' => $district['name'],
                        'bn_name' => $district['bn_name'],
                        'lat' => $district['lat'] ?? null,
                        'long' => $district['long'] ?? null,
                    ]
                );
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Districts imported successfully.');

        // 3. Import Upazilas
        $this->info('Fetching upazilas data...');
        $upazilasResponse = Http::get('https://raw.githubusercontent.com/ifahimreza/bangladesh-geojson/master/src/data/bd-upazilas.json');

        if ($upazilasResponse->failed()) {
            $this->error('Failed to fetch upazilas data from API.');
            return self::FAILURE;
        }

        $upazilas = $upazilasResponse->json('upazilas') ?? [];
        $this->info('Importing/updating upazilas in database...');
        
        $bar = $this->output->createProgressBar(count($upazilas));
        $bar->start();

        DB::transaction(function () use ($upazilas, $bar) {
            foreach ($upazilas as $upazila) {
                Upazila::updateOrCreate(
                    ['id' => $upazila['id']],
                    [
                        'district_id' => $upazila['district_id'],
                        'name' => $upazila['name'],
                        'bn_name' => $upazila['bn_name'],
                    ]
                );
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Upazilas imported successfully.');

        // 4. Import Unions
        $this->info('Fetching unions data...');
        $unionsResponse = Http::get('https://raw.githubusercontent.com/nuhil/bangladesh-geocode/master/unions/unions.json');

        if ($unionsResponse->failed()) {
            $this->error('Failed to fetch unions data from API.');
            return self::FAILURE;
        }

        $json = $unionsResponse->json() ?? [];
        $unions = [];
        foreach ($json as $item) {
            if (isset($item['type']) && $item['type'] === 'table' && $item['name'] === 'unions') {
                $unions = $item['data'] ?? [];
                break;
            }
        }

        $this->info('Importing/updating unions in database (this might take a few moments)...');
        
        $upazilaIds = Upazila::pluck('id')->toArray();
        $upazilaIdsSet = array_flip($upazilaIds);

        $bar = $this->output->createProgressBar(count($unions));
        $bar->start();

        DB::transaction(function () use ($unions, $upazilaIdsSet, $bar) {
            foreach ($unions as $union) {
                // Ensure the referenced upazila exists to prevent foreign key errors
                if (isset($union['upazilla_id']) && isset($upazilaIdsSet[$union['upazilla_id']])) {
                    Union::updateOrCreate(
                        ['id' => $union['id']],
                        [
                            'upazila_id' => $union['upazilla_id'],
                            'name' => $union['name'],
                            'bn_name' => $union['bn_name'],
                            'url' => $union['url'] ?? null,
                        ]
                    );
                }
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Unions imported successfully.');

        $this->info('=====================================');
        $this->info('All Bangladesh locations imported successfully!');
        
        return self::SUCCESS;
    }
}
