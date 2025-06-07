<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Grupo;
use App\Models\Subgrupo;
use App\Models\Clase;
use App\Models\Subclase;

class CogSeeder extends Seeder
{
    private array $tiposGastoMap = [
        'GASTO CORRIENTE' => '1',
        'GASTO DE CAPITAL' => '2',
        'AMORTIZACIÓN DE LA DEUDA Y DISMINUCIÓN DE PASIVOS' => '3',
        'PENSIONES Y JUBILACIONES' => '4',
        'PARTICIPACIONES' => '5',
        'TRANSITORIA' => '6',
    ];

    public function run()
    {
        $this->command->info('Importando COG desde cog.csv...');

        $path = database_path('seeders/cog.csv');
        if (!file_exists($path)) {
            $this->command->error("Archivo cog.csv no encontrado en: $path");
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $grupos = [];
        $subgrupos = [];
        $clases = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;

            // Extraer COG, nombre y tipo de gasto
            if (!preg_match('/^(\d{1,4})\s+([^\[]+)(?:\[(.*?)\])?$/u', $line, $matches)) {
                continue;
            }

            $cog = $matches[1];
            $nombre = trim($matches[2]);
            $tipoGastoTexto = $matches[3] ?? null;
            $tipoGasto = $this->mapTipoGasto($tipoGastoTexto);

            $nivel = strlen($cog);

            switch ($nivel) {
                case 1: // Grupo
                    $grupo = Grupo::updateOrCreate(
                        ['clave' => $cog],
                        ['nombre' => $nombre]
                    );
                    $grupos[$cog] = $grupo->id_grupo;
                    break;

                case 2: // Subgrupo
                    $grupoClave = substr($cog, 0, 1);
                    $subgrupoClave = substr($cog, 1, 1);
                    $grupoId = $grupos[$grupoClave] ?? Grupo::where('clave', $grupoClave)->value('id_grupo');

                    if (!$grupoId) continue 2;

                    $subgrupo = Subgrupo::updateOrCreate(
                        ['clave' => $subgrupoClave, 'id_grupo' => $grupoId],
                        ['nombre' => $nombre]
                    );
                    $subgrupos[$cog] = $subgrupo->id_subgrupo;
                    break;

                case 3: // Clase
                    $grupoClave = substr($cog, 0, 1);
                    $subgrupoClave = substr($cog, 1, 1);
                    $claseClave = substr($cog, 2, 1);
                    $subgrupoKey = $grupoClave . $subgrupoClave;

                    $subgrupoId = $subgrupos[$subgrupoKey] ?? Subgrupo::where('clave', $subgrupoClave)
                        ->whereHas('grupo', function ($q) use ($grupoClave) {
                            $q->where('clave', $grupoClave);
                        })
                        ->value('id_subgrupo');

                    if (!$subgrupoId) continue 2;

                    $clase = Clase::updateOrCreate(
                        ['clave' => $claseClave, 'id_subgrupo' => $subgrupoId],
                        ['nombre' => $nombre]
                    );
                    $clases[$cog] = $clase->id_clase;
                    break;

                case 4: // Subclase
                    $grupoClave = substr($cog, 0, 1);
                    $subgrupoClave = substr($cog, 1, 1);
                    $claseClave = substr($cog, 2, 1);
                    $subclaseClave = substr($cog, 3, 1);
                    $claseKey = $grupoClave . $subgrupoClave . $claseClave;

                    $claseId = $clases[$claseKey] ?? Clase::where('clave', $claseClave)
                        ->whereHas('subgrupo', function ($q) use ($subgrupoClave, $grupoClave) {
                            $q->where('clave', $subgrupoClave)
                              ->whereHas('grupo', function ($q2) use ($grupoClave) {
                                  $q2->where('clave', $grupoClave);
                              });
                        })
                        ->value('id_clase');

                    if (!$claseId) continue 2;

                    Subclase::updateOrCreate(
                        ['clave' => $subclaseClave, 'id_clase' => $claseId],
                        ['nombre' => $nombre, 'tipo_gasto' => $tipoGasto]
                    );
                    break;
            }
        }

        $this->command->info('Importación de COG finalizada correctamente.');
    }

    private function mapTipoGasto(?string $texto): ?string
    {
        if (!$texto) return null;

        $tipos = array_filter(array_map('trim', explode(',', $texto)));
        $ids = [];

        foreach ($tipos as $tipo) {
            $tipo = strtoupper($tipo);
            if (isset($this->tiposGastoMap[$tipo])) {
                $ids[] = $this->tiposGastoMap[$tipo];
            }
        }

        return $ids ? implode('-', array_unique($ids)) : null;
    }
}