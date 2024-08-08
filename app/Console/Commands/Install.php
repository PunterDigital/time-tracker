<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Jetstream;
use PDO;
use PDOException;

class Install extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up and installs the application, database migrations, permissions etc.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting the installation process...');

        $this->checkRequirements();
        $this->installNpmDependencies();
        $this->setupEnvFile();
        $this->setupDatabase();

        // Clear config cache
        $this->clearConfigCache();

        // Reload configuration
        $this->reloadConfiguration();

        // Verify database connection
        $this->verifyDatabaseConnection();

        // Wait for database to be ready
        $this->waitForDatabase();

        $this->runMigrations();
        $this->seedDatabase();
        $this->setupRolesAndPermissions();

        $this->info('Installation completed successfully!');
    }

    private function checkRequirements()
    {
        $this->info('Checking minimum requirements...');

        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $this->error('PHP version 8.0 or higher is required.');
            exit(1);
        }

        $nodeVersion = trim(shell_exec('node -v'));
        if (version_compare(substr($nodeVersion, 1), '18.0.0', '<')) {
            $this->error('Node.js version 18 or higher is required.');
            exit(1);
        }

        $this->info('Minimum requirements met.');
    }

    private function installNpmDependencies()
    {
        $this->info('Installing npm dependencies...');
        shell_exec('npm install');
        shell_exec('npm run build');
    }

    private function setupEnvFile()
    {
        if (!file_exists('.env')) {
            $this->info('Creating .env file...');
            copy('.env.example', '.env');
            Artisan::call('key:generate');
        }
    }

    private function setupDatabase()
    {
        $this->info('Setting up database...');

        $this->dbConfig['database'] = $this->ask('Enter the database name:');
        $this->dbConfig['username'] = $this->ask('Enter the database username:');
        $this->dbConfig['password'] = $this->secret('Enter the database password:');
        $this->dbConfig['host'] = $this->ask('Enter the database host:', '127.0.0.1');
        $this->dbConfig['port'] = $this->ask('Enter the database port:', '3306');

        // Update .env file with database credentials
        $this->updateEnvFile([
            'DB_DATABASE' => $this->dbConfig['database'],
            'DB_USERNAME' => $this->dbConfig['username'],
            'DB_PASSWORD' => $this->dbConfig['password'],
            'DB_HOST' => $this->dbConfig['host'],
            'DB_PORT' => $this->dbConfig['port'],
        ]);

        // Attempt to connect to the database server (without specifying a database)
        $retry = true;
        while ($retry) {
            try {
                $dsn = "mysql:host={$this->dbConfig['host']};port={$this->dbConfig['port']};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5,
                ];
                $pdo = new PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], $options);

                // Check if the database exists
                $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbConfig['database']}'");
                $databaseExists = $stmt->fetch();

                if (!$databaseExists) {
                    if ($this->confirm("The database '{$this->dbConfig['database']}' does not exist. Do you want to create it?", true)) {
                        $this->createDatabase($pdo, $this->dbConfig['database']);
                    } else {
                        $this->error('Database does not exist. Please create the database manually and run the installer again.');
                        exit(1);
                    }
                }

                $this->info('Database connection successful.');
                $retry = false;

            } catch (PDOException $e) {
                $this->error('Database connection failed: ' . $e->getMessage());
                if ($this->confirm('Do you want to try again?', true)) {
                    $this->dbConfig['host'] = $this->ask('Enter the database host:', '127.0.0.1');
                    $this->dbConfig['port'] = $this->ask('Enter the database port:', '3306');
                    $this->updateEnvFile([
                        'DB_HOST' => $this->dbConfig['host'],
                        'DB_PORT' => $this->dbConfig['port'],
                    ]);
                } else {
                    $this->error('Installation aborted.');
                    exit(1);
                }
            }
        }
    }

    private function reloadConfiguration()
    {
        $this->info('Reloading configuration...');

        // Reload the configuration
        foreach ($this->dbConfig as $key => $value) {
            Config::set("database.connections.mysql.{$key}", $value);
        }

        // Reconnect to the database with new configuration
        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->info('Configuration reloaded.');
    }

    private function verifyDatabaseConnection()
    {
        $this->info('Verifying database connection...');

        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $this->info("Connected successfully to the database: {$dbName}");
        } catch (\Exception $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            $this->error('Please check your database configuration and try again.');
            exit(1);
        }
    }

    private function createDatabase($pdo, $dbName)
    {
        try {
            $charset = config("database.connections.mysql.charset", 'utf8mb4');
            $collation = config("database.connections.mysql.collation", 'utf8mb4_unicode_ci');

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET $charset COLLATE $collation");
            $this->info("Database '$dbName' created successfully.");
        } catch (PDOException $e) {
            $this->error('Failed to create database: ' . $e->getMessage());
            exit(1);
        }
    }

    private function clearConfigCache()
    {
        $this->info('Clearing configuration cache...');
        Artisan::call('config:clear');
        $this->info('Configuration cache cleared.');
    }

    private function waitForDatabase()
    {
        $this->info('Waiting for database to be ready...');
        $maxAttempts = 10;
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            try {
                DB::connection()->getPdo();
                $this->info('Database is ready.');
                return;
            } catch (\Exception $e) {
                $this->warn("Database not ready, retrying... (Attempt " . ($attempt + 1) . "/" . $maxAttempts . ")");
                sleep(2);
                $attempt++;
            }
        }
        $this->error('Failed to connect to the database after multiple attempts.');
        exit(1);
    }

    private function runMigrations()
    {
        $this->info('Running database migrations...');

        // Check if migrations table exists
        if (!$this->checkMigrationsTable()) {
            $this->warn('Migrations table does not exist. Creating it...');
            Artisan::call('migrate:install');
        }

        // Get current migration status
        $beforeMigrations = $this->getMigrationList();
        $this->info('Current migrations: ' . implode(', ', $beforeMigrations));

        try {
            $output = new \Symfony\Component\Console\Output\BufferedOutput;
            Artisan::call('migrate', ['--force' => true, '--verbose' => true], $output);

            $this->info('Migration command output:');
            $this->line($output->fetch());

            // Check which migrations were actually run
            $afterMigrations = $this->getMigrationList();
            $newMigrations = array_diff($afterMigrations, $beforeMigrations);

            if (empty($newMigrations)) {
                $this->warn('No new migrations were run. This might be because:');
                $this->warn('1. All migrations have already been run.');
                $this->warn('2. There are no migration files in the database/migrations directory.');
                $this->warn('3. There might be an issue preventing migrations from running.');

                if ($this->option('verbose')) {
                    $this->info('Migrations directory content:');
                    $files = scandir(database_path('migrations'));
                    $this->line(implode(PHP_EOL, $files));
                }
            } else {
                $this->info('New migrations run: ' . implode(', ', $newMigrations));
            }

            $this->info('Migrations completed.');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->error('Full exception:');
            $this->error($e->getTraceAsString());
            if ($this->confirm('Do you want to try running migrations again?', true)) {
                $this->runMigrations();
            } else {
                $this->error('Installation aborted.');
                exit(1);
            }
        }
    }

    private function checkMigrationsTable()
    {
        try {
            return DB::table('migrations')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getMigrationList()
    {
        try {
            return DB::table('migrations')->pluck('migration')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function seedDatabase()
    {
        $this->info('Seeding the database...');
        Artisan::call('db:seed');
    }

    private function setupRolesAndPermissions()
    {
        $this->info('Setting up roles and permissions...');

        Jetstream::role('admin', 'Administrator', [
            'projects:viewAll',
            'projects:view',
            'projects:edit',
            'projects:delete',
            'projects:modifyUsers',
            'time:track',
            'time:view',
            'time:viewAll',
            'time:edit',
            'time:delete',
            'reports:view',
        ])->description('Administrator users can perform any action.');

        Jetstream::role('employee', 'Employee', [
            'projects:view',
            'time:view',
            'time:track',
        ])->description('Employee users can view projects and manage their own time tracking.');
    }

    private function updateEnvFile($data)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            foreach ($data as $key => $value) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            }

            file_put_contents($path, $content);
        }
    }
}
