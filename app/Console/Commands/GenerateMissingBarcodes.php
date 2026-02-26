<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateMissingBarcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:generate-barcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an 8-character alphanumeric barcode for all members missing one.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('role', 'member')->whereNull('barcode')->get();

        if ($users->isEmpty()) {
            $this->info('All members already have a barcode.');
            return;
        }

        $count = 0;
        foreach ($users as $user) {
            $user->barcode = User::generateUniqueBarcode();
            $user->save();
            $count++;
        }

        $this->info("Successfully generated barcodes for {$count} members.");
    }
}
