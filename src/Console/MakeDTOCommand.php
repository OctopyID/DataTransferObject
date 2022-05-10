<?php

namespace Octopy\DTO\Console;

use Exception;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeDTOCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:data';

    /**
     * @var string
     */
    protected $signature = 'make:data {name : The name of the class}
                            {--m|model= : The model that the data applies}
                            {--f|force : Create the class even if the DTO already exists}';

    /**
     * @var string
     */
    protected $description = 'Create a new DTO class';

    /**
     * @var string
     */
    protected $type = 'DTO';

    /**
     * @param  Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * @return void
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function handle() : void
    {
        $name = $this->qualifyClass($this->getNameInput());

        if (! $this->hasOption('force') || ! $this->option('force')) {
            if ($this->alreadyExists($this->getNameInput())) {
                $this->error($name . ' already exists!');

                return;
            }
        }

        $this->makeDirectory(
            $path = $this->getPath($name)
        );

        $stub = $this->buildClass($name);

        if ($this->hasOption('model') && $this->option('model')) {
            if (! class_exists('Doctrine\DBAL\Schema\AbstractSchemaManager')) {
                throw new Exception('Doctrine DBAL is not installed. run "composer require doctrine/dbal"');
            }

            $stub = $this->replaceStub($stub);
        }

        $this->files->put($path, $this->sortImports(
            $stub
        ));

        $this->info($name . ' created successfully.');
    }

    /**
     * @param  string $stub
     * @return string
     * @throws Exception
     */
    protected function replaceStub(string $stub) : string
    {
        $replace = [
            '{{ model }}' => $this->option('model'),
        ];

        $model = $this->qualifyModel($this->option('model'));
        if ($this->hasOption('model') && $model) {
            if (! class_exists($model)) {
                throw new Exception("Model [$model] does not exist");
            }

            $model = App::make($model);

            $table = $model->getTable();

            $columns = collect(Schema::getColumnListing($table));
            if (! empty($model->getFillable())) {
                $columns = $model->getFillable();
            }

            $columns = collect($columns)->flatMap(function (string $column) use ($table) : array {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $type = $this->getTypeData(
                        DB::getSchemaBuilder()->getColumnType($table, $column)
                    );
                } catch (Exception $e) {
                    $type = '';
                }

                return [
                    $column => $type,
                ];
            });

            $data = [];
            foreach ($columns as $column => $type) {
                $method = Str::of($column)->camel()->ucfirst()->toString();

                if ($method === 'Uuid') {
                    $method = 'UUID';
                }

                $data[] = '';

                if (! str_contains($type, 'Carbon')) {
                    $data[] = '/**';
                    $data[] = ' * @return ' . $type;
                    $data[] = ' */';
                    $data[] = 'public function get' . $method . '() : ' . $type;
                    $data[] = '{';
                    $data[] = '    return $this->get(\'' . $column . '\');';
                    $data[] = '}';
                } else {
                    $data[] = '/**';
                    $data[] = ' * @return \Illuminate\Support\Carbon';
                    $data[] = ' */';
                    $data[] = 'public function get' . $method . '() : \Illuminate\Support\Carbon';
                    $data[] = '{';
                    $data[] = '    return \Illuminate\Support\Carbon::parse($this->get(\'' . $column . '\'));';
                    $data[] = '}';
                }

                $data[] = '';

                $data[] = '/**';
                $data[] = ' * @param  ' . $type . ' $value';
                $data[] = ' * @return ' . $this->getNameInput();
                $data[] = ' */';
                $data[] = 'public function set' . $method . '(' . $type . ' $value) : ' . $this->getNameInput();
                $data[] = '{';
                $data[] = '    return $this->set(\'' . $column . '\', $value);';
                $data[] = '}';
            }

            $replace['//'] = trim(collect($data)->map(function ($line) {
                return '    ' . $line;
            })
                ->implode("\n"));
        }

        return str_replace(array_keys($replace), array_values($replace), $stub);
    }

    /**
     * @return string
     */
    protected function getStub() : string
    {
        if ($this->option('model')) {
            return __DIR__ . '/../../stubs/dto.full.stub';
        }

        return __DIR__ . '/../../stubs/dto.mini.stub';
    }

    /**
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace) : string
    {
        return $rootNamespace . '\\DTO';
    }

    /**
     * @param  string $type
     * @return string
     */
    protected function getTypeData(string $type) : string
    {
        return match ($type) {
            'boolean' => 'bool',
            'float', 'double' => 'float',
            'bigint', 'integer' => 'int',
            'json', 'array' => 'array',
            'string', 'enum', 'text' => 'string',
            'datetime' => '\Illuminate\Support\Carbon|string',
            default => $type,
        };
    }
}
