<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $base = [
            'id'          => $this->id,
            'nombre'      => trim($this->usua_nombre.' '.$this->usua_apellido_p.($this->usua_apellido_m ? ' '.$this->usua_apellido_m : '')),
            'rut'         => $this->usua_rut.'-'.$this->usua_dv,
            'correo'      => $this->usua_correo,
            'rolNombre'   => $this->rol?->role_nombre,
            'roleId'      => $this->role_id,
            'totalCajas'  => $this->whenNotNull(
                $this->usuarios_por_caja_count,
                fn () => (int) $this->usuarios_por_caja_count,
            ),
        ];

        // El detalle (show) incluye los campos individuales para edición y las cajas cargadas
        if ($this->relationLoaded('usuariosPorCaja')) {
            $base['apellidoP']  = $this->usua_apellido_p;
            $base['apellidoM']  = $this->usua_apellido_m;
            $base['dv']         = $this->usua_dv;
            $base['fechaNac']   = $this->usua_fecha_nac?->format('Y-m-d');

            $base['cajas'] = $this->usuariosPorCaja->map(fn ($uc) => [
                'id'          => $uc->id,
                'cajaId'      => $uc->caja_id,
                'cajaNombre'  => $uc->caja?->caja_nombre,
                'localNombre' => $uc->caja?->local?->loca_nombre,
                'habilitado'  => $uc->usca_habilitado,
                'fechaInicio' => $uc->usca_fecha_inicio?->format('Y-m-d'),
            ])->values();
        }

        return $base;
    }
}
