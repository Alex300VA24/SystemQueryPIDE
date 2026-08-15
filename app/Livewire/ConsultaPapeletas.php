<?php

namespace App\Livewire;

class ConsultaPapeletas extends BaseConsultation
{
    protected function page(): array
    {
        return [
            'title' => 'Consulta de papeletas', 'source' => 'Tránsito', 'description' => 'Consulta infracciones asociadas a una placa vehicular.', 'field' => 'Placa', 'placeholder' => 'Ej. ABC-123', 'hint' => 'Usa formato peruano de placa.', 'rules' => ['required', 'regex:/^[A-Za-z0-9]{3}-?[A-Za-z0-9]{3}$/'], 'accent' => '#a15c00',
            'result' => ['Placa' => 'ABC-123', 'Infracciones pendientes' => '1', 'Código' => 'G47', 'Fecha' => '03/08/2026', 'Importe referencial' => 'S/ 428.00', 'Estado' => 'Pendiente'],
        ];
    }
}
