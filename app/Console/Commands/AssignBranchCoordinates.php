<?php

namespace App\Console\Commands;

use App\Models\Branch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AssignBranchCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branches:assign-coordinates {--dry-run : Run without saving to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign coordinates to branches that don\'t have them using geocoding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no changes will be saved');
        }

        // Get branches without coordinates
        $branches = Branch::with('business')
            ->where(function($query) {
                $query->whereNull('latitude')
                      ->orWhereNull('longitude');
            })
            ->get();

        if ($branches->isEmpty()) {
            $this->info('No branches need coordinate assignment!');
            return 0;
        }

        $this->info("Found {$branches->count()} branches without coordinates\n");

        $successCount = 0;
        $failCount = 0;

        foreach ($branches as $branch) {
            $this->line("Processing: {$branch->business->name} - {$branch->name}");
            
            // Build search query
            $searchQuery = $this->buildSearchQuery($branch);
            $this->line("  Search: {$searchQuery}");

            try {
                // Call Nominatim API for geocoding
                // Nominatim requires a User-Agent header
                $response = Http::withHeaders([
                    'User-Agent' => 'POS-Supermarket-App/1.0',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $searchQuery,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'gh', // Restrict to Ghana
                ]);

                if ($response->successful() && count($response->json()) > 0) {
                    $result = $response->json()[0];
                    $lat = (float) $result['lat'];
                    $lng = (float) $result['lon'];

                    $this->info("  ✓ Found coordinates: {$lat}, {$lng}");
                    $this->line("  Location: {$result['display_name']}");

                    if (!$dryRun) {
                        $branch->latitude = $lat;
                        $branch->longitude = $lng;
                        $branch->save();
                        $this->info("  ✓ Saved to database");
                    } else {
                        $this->comment("  (Would save: lat={$lat}, lng={$lng})");
                    }

                    $successCount++;
                } else {
                    $this->error("  ✗ No coordinates found");
                    $failCount++;
                }

            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $failCount++;
            }

            $this->line('');
            
            // Rate limiting - be nice to Nominatim
            sleep(1);
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Success: {$successCount}");
        $this->error("  Failed: {$failCount}");

        if ($dryRun) {
            $this->newLine();
            $this->comment('DRY RUN - No changes were saved. Run without --dry-run to save coordinates.');
        }

        return 0;
    }

    /**
     * Build a search query from branch information
     */
    private function buildSearchQuery(Branch $branch): string
    {
        $parts = [];

        // Add address (extract meaningful parts)
        if ($branch->address && strlen($branch->address) > 2) {
            // Extract city names from address if present
            $address = $branch->address;
            
            // Common Ghana cities
            $cities = ['Accra', 'Kumasi', 'Takoradi', 'Tamale', 'Cape Coast', 'Sekondi', 
                      'Tema', 'Obuasi', 'Koforidua', 'Sunyani', 'Ho'];
            
            foreach ($cities as $city) {
                if (stripos($address, $city) !== false) {
                    $parts[] = $city;
                    break;
                }
            }
            
            // If no city found, try to use address parts
            if (empty($parts)) {
                // Clean up address - remove numbers and short words
                $addressParts = explode(',', $address);
                foreach ($addressParts as $part) {
                    $part = trim($part);
                    if (strlen($part) > 4 && !is_numeric($part)) {
                        $parts[] = $part;
                        break;
                    }
                }
            }
        }

        // Add region
        if ($branch->region && $branch->region !== 'N/A') {
            $parts[] = $branch->region;
        }

        // If we still don't have parts, use region name as location
        if (empty($parts) && $branch->region && $branch->region !== 'N/A') {
            // Use region capital as fallback
            $regionCapitals = [
                'Greater Accra' => 'Accra',
                'Ashanti' => 'Kumasi',
                'Western' => 'Takoradi',
                'Eastern' => 'Koforidua',
                'Central' => 'Cape Coast',
                'Northern' => 'Tamale',
                'Upper East' => 'Bolgatanga',
                'Upper West' => 'Wa',
                'Volta' => 'Ho',
                'Brong Ahafo' => 'Sunyani',
            ];
            
            if (isset($regionCapitals[$branch->region])) {
                $parts = [$regionCapitals[$branch->region], $branch->region];
            }
        }

        // Always add Ghana
        $parts[] = 'Ghana';

        return implode(', ', $parts);
    }
}
