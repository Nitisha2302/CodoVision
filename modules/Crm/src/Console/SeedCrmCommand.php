<?php

namespace Codovision\Crm\Console;

use Codovision\Crm\Database\Seeders\CrmDatabaseSeeder;
use Illuminate\Console\Command;

class SeedCrmCommand extends Command
{
    protected $signature = 'crm:seed';

    protected $description = 'Seed the isolated CodoVision CRM module (roles, statuses, demo users)';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => CrmDatabaseSeeder::class]);
        $this->info('CRM seeded. Login at /crm/login as admin@codovision.tech / Admin@12345');

        return self::SUCCESS;
    }
}
