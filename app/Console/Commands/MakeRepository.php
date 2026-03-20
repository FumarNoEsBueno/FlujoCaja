<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    protected $signature = 'make:repository
                            {name : Nombre del repositorio (ej: Dashboard, ProductoVenta)}
                            {--no-dto : Omite la generación del DTO}';

    protected $description = 'Genera la estructura completa de un Repository: Interface, Eloquent y DTO';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $withDto = ! $this->option('no-dto');
        $basePath = app_path("Repositories/{$name}");

        if (is_dir($basePath)) {
            $this->error("El repositorio [{$name}] ya existe en app/Repositories/{$name}");

            return self::FAILURE;
        }

        $this->createDirectories($basePath, $withDto);
        $this->createInterface($basePath, $name);
        $this->createEloquent($basePath, $name);

        if ($withDto) {
            $this->createDto($basePath, $name);
        }

        $this->newLine();
        $this->info("✅  Repositorio [{$name}] generado exitosamente.");
        $this->newLine();
        $this->printSummary($name, $withDto);
        $this->printNextSteps($name);

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Directorios
    // ─────────────────────────────────────────────────────────────────────────

    private function createDirectories(string $basePath, bool $withDto): void
    {
        $dirs = [
            "{$basePath}/Interfaces",
            "{$basePath}/Eloquent",
        ];

        if ($withDto) {
            $dirs[] = "{$basePath}/DTOs";
        }

        foreach ($dirs as $dir) {
            mkdir($dir, 0755, true);
            $this->line("  <fg=gray>created</> {$this->relativePath($dir)}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Interface
    // ─────────────────────────────────────────────────────────────────────────

    private function createInterface(string $basePath, string $name): void
    {
        $namespace = "App\\Repositories\\{$name}\\Interfaces";
        $interface = "{$name}RepositoryInterface";
        $model = "App\\Models\\{$name}";
        $dtoNs = "App\\Repositories\\{$name}\\DTOs\\Store{$name}DTO";

        $stub = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use {$model};
        use {$dtoNs};
        use Illuminate\Pagination\AbstractPaginator;

        interface {$interface}
        {
            /**
             * Lista registros paginados.
             */
            public function table(): AbstractPaginator;

            /**
             * Crea un nuevo registro y lo retorna.
             */
            public function store(Store{$name}DTO \$dto): {$name};

            /**
             * Retorna un registro por su ID.
             */
            public function show(int \$id): {$name};

            /**
             * Elimina un registro por su ID.
             */
            public function destroy(int \$id): void;
        }
        PHP;

        $file = "{$basePath}/Interfaces/{$interface}.php";
        file_put_contents($file, $stub);
        $this->line("  <fg=green>created</> {$this->relativePath($file)}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Eloquent Repository
    // ─────────────────────────────────────────────────────────────────────────

    private function createEloquent(string $basePath, string $name): void
    {
        $namespace = "App\\Repositories\\{$name}\\Eloquent";
        $class = "{$name}Repository";
        $interface = "{$name}RepositoryInterface";
        $model = $name;
        $modelFqn = "App\\Models\\{$name}";
        $dtoFqn = "App\\Repositories\\{$name}\\DTOs\\Store{$name}DTO";
        $ifaceFqn = "App\\Repositories\\{$name}\\Interfaces\\{$interface}";

        $stub = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use {$modelFqn};
        use {$dtoFqn};
        use {$ifaceFqn};
        use Illuminate\Pagination\AbstractPaginator;

        class {$class} implements {$interface}
        {
            private const PER_PAGE = 10;

            public function table(): AbstractPaginator
            {
                return {$model}::orderBy('created_at', 'desc')
                    ->simplePaginate(self::PER_PAGE);
            }

            public function store(Store{$name}DTO \$dto): {$model}
            {
                return {$model}::create([
                    // TODO: mapear campos del DTO al modelo
                ]);
            }

            public function show(int \$id): {$model}
            {
                /** @var {$model} \$record */
                \$record = {$model}::findOrFail(\$id);

                return \$record;
            }

            public function destroy(int \$id): void
            {
                {$model}::findOrFail(\$id)->delete();
            }
        }
        PHP;

        $file = "{$basePath}/Eloquent/{$class}.php";
        file_put_contents($file, $stub);
        $this->line("  <fg=green>created</> {$this->relativePath($file)}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DTO
    // ─────────────────────────────────────────────────────────────────────────

    private function createDto(string $basePath, string $name): void
    {
        $namespace = "App\\Repositories\\{$name}\\DTOs";
        $class = "Store{$name}DTO";

        $stub = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Illuminate\Http\Request;

        final readonly class {$class}
        {
            public function __construct(
                // TODO: definir propiedades del DTO
            ) {}

            public static function fromRequest(Request \$request): self
            {
                return new self(
                    // TODO: mapear campos del request
                );
            }
        }
        PHP;

        $file = "{$basePath}/DTOs/{$class}.php";
        file_put_contents($file, $stub);
        $this->line("  <fg=green>created</> {$this->relativePath($file)}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Output helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function printSummary(string $name, bool $withDto): void
    {
        $this->line('  <fg=yellow>Archivos generados:</>');
        $this->line("  • app/Repositories/{$name}/Interfaces/{$name}RepositoryInterface.php");
        $this->line("  • app/Repositories/{$name}/Eloquent/{$name}Repository.php");

        if ($withDto) {
            $this->line("  • app/Repositories/{$name}/DTOs/Store{$name}DTO.php");
        }

        $this->newLine();
    }

    private function printNextSteps(string $name): void
    {
        $binding = "{$name}RepositoryInterface::class => {$name}Repository::class";
        $providerNs = "use App\\Repositories\\{$name}\\Interfaces\\{$name}RepositoryInterface;";
        $repoNs = "use App\\Repositories\\{$name}\\Eloquent\\{$name}Repository;";

        $this->line('  <fg=yellow>Próximos pasos:</>');
        $this->line('  1. Registrá el binding en <fg=cyan>AppServiceProvider.php</>:');
        $this->newLine();
        $this->line("     <fg=gray>{$providerNs}</>");
        $this->line("     <fg=gray>{$repoNs}</>");
        $this->newLine();
        $this->line('     <fg=gray>$this->app->bind(</>');
        $this->line("     <fg=gray>    {$binding}</>");
        $this->line('     <fg=gray>);</>');
        $this->newLine();
        $this->line('  2. Completá los <fg=cyan>TODO</> en el DTO y el Eloquent Repository.');
        $this->line("  3. Creá el Model si no existe:  <fg=cyan>php artisan make:model {$name}</>");
        $this->newLine();
    }

    private function relativePath(string $absolute): string
    {
        return str_replace(base_path().'/', '', $absolute);
    }
}
