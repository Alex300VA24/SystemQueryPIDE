<div class="inicio-container">
    <section class="services-grid" aria-label="Consultas disponibles">
        @foreach ([
            ['key'=>'dni','name'=>'RENIEC','tag'=>'Registro Nacional','description'=>'Registro Nacional de Identificación y Estado Civil','class'=>'reniec','icon'=>'id','badge'=>'shield','features'=>['Consulta por DNI','Datos personales','Estado del documento','Foto y firma digital']],
            ['key'=>'ruc','name'=>'SUNAT','tag'=>'Administración Tributaria','description'=>'Superintendencia Nacional de Aduanas y de Administración Tributaria','class'=>'sunat','icon'=>'document','badge'=>'calculator','features'=>['Consulta por RUC','Razón social','Estado del contribuyente','Domicilio fiscal']],
            ['key'=>'partidas','name'=>'SUNARP','tag'=>'Registros Públicos','description'=>'Superintendencia Nacional de los Registros Públicos','class'=>'sunarp','icon'=>'home','badge'=>'archive','features'=>['Consulta registral','Propiedades inmuebles','Vehículos registrados','Personas jurídicas']],
            ['key'=>'ruc','tab'=>'ccoactiva','name'=>'Cobranza Coactiva','tag'=>'Administración Tributaria','description'=>'Deudas en cobranza coactiva administradas por SUNAT','class'=>'sunat','icon'=>'calculator','badge'=>'shield','features'=>['Consulta por DNI o RUC','Entidad de la deuda','Periodo tributario','Monto de la deuda']],
            ['key'=>'cert-ambientales','name'=>'Certificaciones Ambientales','tag'=>'Gestión Ambiental','description'=>'Registro Administrativo de Certificaciones Ambientales - SENACE','class'=>'senace','icon'=>'leaf','badge'=>'archive','features'=>['Consulta por expediente','Sector y actividad','Estado de evaluación','Enlaces al expediente digital']],
            ['key'=>'mtc','name'=>'MTC','tag'=>'Transportes y Comunicaciones','description'=>'Récord de conductor: licencias, papeletas y sanciones vigentes','class'=>'mtc','icon'=>'car','badge'=>'shield','features'=>['Consulta por DNI o CE','Última licencia emitida','Papeletas aplicadas','Sanciones vigentes']],
        ] as $service)
            @if ($this->canReach($service['key']))
            <article class="service-card {{ $service['class'] }}-card">
                <div class="service-card-body">
                    <div class="service-card-top">
                        <div class="service-card-title">
                            <span class="service-icon"><x-icon :name="$service['icon']" /></span>
                            <div>
                                <h3>{{ $service['name'] }}</h3>
                                <span>{{ $service['tag'] }}</span>
                            </div>
                        </div>
                        <span class="service-badge"><x-icon :name="$service['badge']" /></span>
                    </div>

                    <p class="service-description">{{ $service['description'] }}</p>

                    <ul class="service-features">
                        @foreach ($service['features'] as $feature)
                            <li><span class="feature-check"><x-icon name="check" /></span>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    <button type="button" wire:click="selectSection('{{ $service['key'] }}', '{{ $service['tab'] ?? '' }}')" class="service-btn btn-{{ $service['class'] }}">
                        <x-icon name="search" /> Consultar {{ $service['name'] }}
                    </button>
                </div>
            </article>
            @endif
        @endforeach
    </section>

    <footer class="inicio-footer glass">
        <p><x-icon name="info" /> <strong>Sistema de Consultas PIDE v2.0</strong> <span class="sep">|</span> Plataforma de Interoperabilidad del Estado Peruano</p>
        <p class="footer-note">Acceso autorizado únicamente para entidades del Estado</p>
    </footer>
</div>
