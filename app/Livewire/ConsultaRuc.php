<?php

namespace App\Livewire;

use App\Services\Pide\Contracts\SunatServiceInterface;

class ConsultaRuc extends BaseConsultation
{
    protected function page(): array
    {
        return [
            'title' => 'Consulta RUC', 'source' => 'SUNAT', 'description' => 'Superintendencia Nacional de Aduanas y de Administración Tributaria', 'field' => 'Número de RUC', 'placeholder' => 'Ingrese 11 dígitos', 'hint' => 'Ingresa los 11 dígitos del contribuyente.', 'rules' => 'required|digits:11', 'accent' => '#dc2626', 'icon' => 'search',
            'fullWidthFields' => ['RUC', 'Nombre y/o Razón Social', 'Actividad Económica', 'Nombre de Vía', 'Nombre de la Zona', 'Referencia'],
            'resultTitle' => 'Información del Contribuyente',
            'featuredFields' => ['RUC' => 'ruc', 'Nombre y/o Razón Social' => 'business-name'],
            'result' => [
                'RUC' => '20123456789',
                'Nombre y/o Razón Social' => 'Servicios Municipales Demo S.A.C.',
                'Estado del Contribuyente' => 'Activo',
                'Tipo de Persona' => 'Persona Jurídica',
                'Tipo de Contribuyente' => 'Sociedad Anónima Cerrada',
                'Actividad Económica' => 'Actividades de servicios',
                'Fecha de Alta' => '-',
                'Fecha de Baja' => '-',
                'Fecha de Actualización' => '-',
                'Código de Ubigeo' => '150101',
                'Departamento' => 'Lima',
                'Provincia' => 'Lima',
                'Distrito' => 'Lima',
                'Tipo de Vía' => 'Avenida',
                'Nombre de Vía' => 'Institucional',
                'Número' => '245',
                'Interior' => '-',
                'Tipo de Zona' => '-',
                'Nombre de la Zona' => '-',
                'Referencia' => '-',
                'Condición del Domicilio' => 'Habido',
                'Estado Activo' => 'SÍ',
                'Estado Habido' => 'SÍ',
                'Dependencia' => '-',
                'Código Secuencia' => '-',
            ],
        ];
    }

    protected function attemptReal(): ?array
    {
        $resultado = app(SunatServiceInterface::class)->consultarRUC($this->busqueda);

        if (! $resultado['success'] || empty($resultado['data'])) {
            $this->errorMessage = $resultado['message'] ?? 'SUNAT no devolvió resultados.';

            return null;
        }

        $data = $resultado['data'];

        return [
            'RUC' => $data['ruc'] ?? $this->busqueda,
            'Nombre y/o Razón Social' => $data['razon_social'] ?? '',
            'Estado del Contribuyente' => $data['estado_contribuyente'] ?? '',
            'Tipo de Persona' => $data['tipo_persona'] ?? '',
            'Tipo de Contribuyente' => $data['tipo_contribuyente'] ?? '',
            'Actividad Económica' => $data['actividad_economica'] ?? '',
            'Fecha de Alta' => $data['fecha_alta'] ?? '',
            'Fecha de Baja' => $data['fecha_baja'] ?? '',
            'Fecha de Actualización' => $data['fecha_actualizacion'] ?? '',
            'Código de Ubigeo' => $data['codigo_ubigeo'] ?? '',
            'Departamento' => $data['departamento'] ?? '',
            'Provincia' => $data['provincia'] ?? '',
            'Distrito' => $data['distrito'] ?? '',
            'Tipo de Vía' => $data['tipo_via'] ?? '',
            'Nombre de Vía' => $data['nombre_via'] ?? '',
            'Número' => $data['numero'] ?? '',
            'Interior' => $data['interior'] ?? '',
            'Tipo de Zona' => $data['tipo_zona'] ?? '',
            'Nombre de la Zona' => $data['nombre_zona'] ?? '',
            'Referencia' => $data['referencia'] ?? '',
            'Condición del Domicilio' => $data['condicion_domicilio'] ?? '',
            'Estado Activo' => $data['estado_activo'] ?? '',
            'Estado Habido' => $data['estado_habido'] ?? '',
            'Dependencia' => $data['dependencia'] ?? '',
            'Código Secuencia' => $data['codigo_secuencia'] ?? '',
        ];
    }
}
