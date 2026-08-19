<div class="consulta-legacy page" style="--source:#166534">
    <section class="glass consulta-legacy-heading">
        <span class="consulta-source-icon"><x-icon name="leaf" /></span>
        <div>
            <h1>Certificaciones Ambientales - SENACE</h1>
            <p>Registro Administrativo de Certificaciones Ambientales de proyectos evaluados o en evaluación</p>
        </div>
    </section>

    <section class="glass consulta-search-card">
        <form wire:submit="buscar" class="consulta-search-form" novalidate>
            <div class="field">
                <label for="tipoIga"><x-icon name="document" /> Tipo de instrumento (IGA)</label>
                <select id="tipoIga" wire:model="tipoIga">
                    <option value="">Seleccione…</option>
                    <option value="01">Plan de Participación Ciudadana</option>
                    <option value="03">Estudio de Impacto Ambiental Detallado</option>
                    <option value="04">Informe Técnico Sustentatorio</option>
                    <option value="05">Modificación de EIA Detallado</option>
                    <option value="09">IGAPRO</option>
                    <option value="10">Declaración de Impacto Ambiental</option>
                    <option value="11">Modificación de Declaración de Impacto Ambiental</option>
                    <option value="12">Estudio de Impacto Ambiental</option>
                    <option value="13">Modificación de Estudio de Impacto Ambiental</option>
                    <option value="14">Estudio de Impacto Ambiental Semidetallado</option>
                    <option value="15">Modificación de EIA Semidetallado</option>
                    <option value="16">Evaluación Ambiental Estratégica</option>
                    <option value="17">Plan Ambiental</option>
                    <option value="18">Plan de Compensación y Reasentamiento</option>
                    <option value="19">Plan de Gestión Ambiental</option>
                    <option value="20">Plan de Manejo Ambiental</option>
                    <option value="21">Plan de Adecuación Ambiental</option>
                    <option value="22">Plan de Abandono</option>
                    <option value="23">Complementario</option>
                </select>
                @error('tipoIga') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field consulta-main-field">
                <label for="expediente"><x-icon name="document" /> N° de expediente</label>
                <input id="expediente" type="text" wire:model="expediente" maxlength="50" autocomplete="off" placeholder="Ej. 2998007">
                @error('expediente') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="grupoSector"><x-icon name="archive" /> Grupo sector</label>
                <select id="grupoSector" wire:model="grupoSector">
                    <option value="">Seleccione…</option>
                    <option value="1">Energía y Minas</option>
                    <option value="2">Transportes y Comunicaciones</option>
                    <option value="3">Agricultura</option>
                    <option value="4">Salud</option>
                    <option value="5">Vivienda, Construcción y Saneamiento</option>
                </select>
                @error('grupoSector') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="subSector"><x-icon name="archive" /> Subsector</label>
                <select id="subSector" wire:model="subSector">
                    <option value="">Seleccione…</option>
                    <option value="1">Minería</option>
                    <option value="3">Transportes</option>
                    <option value="4">Agricultura</option>
                    <option value="5">Salud</option>
                    <option value="6">Vivienda</option>
                    <option value="7">Electricidad</option>
                    <option value="8">Hidrocarburos</option>
                </select>
                @error('subSector') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="actividad"><x-icon name="archive" /> Actividad</label>
                <select id="actividad" wire:model="actividad">
                    <option value="">Seleccione…</option>
                    <option value="1">Minería</option>
                    <option value="3">Transportes</option>
                    <option value="4">Agricultura - Riego</option>
                    <option value="6">Salud</option>
                    <option value="11">Electricidad</option>
                    <option value="12">Hidrocarburos</option>
                </select>
                @error('actividad') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="nroRuc"><x-icon name="id" /> RUC del titular (opcional)</label>
                <input id="nroRuc" type="text" wire:model="nroRuc" maxlength="11" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 11)">
                @error('nroRuc') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="titular"><x-icon name="user" /> Titular (opcional)</label>
                <input id="titular" type="text" wire:model="titular" autocomplete="off">
            </div>

            <div class="field">
                <label for="nomProyecto"><x-icon name="document" /> Nombre del proyecto (opcional)</label>
                <input id="nomProyecto" type="text" wire:model="nomProyecto" autocomplete="off">
            </div>

            <div class="field">
                <label for="nroCatalogo"><x-icon name="archive" /> N° de catálogo (opcional)</label>
                <input id="nroCatalogo" type="text" wire:model="nroCatalogo" autocomplete="off">
            </div>

            <div class="field">
                <label for="resolucion"><x-icon name="document" /> N° de resolución (opcional)</label>
                <input id="resolucion" type="text" wire:model="resolucion" autocomplete="off">
            </div>

            <div class="field">
                <label for="idDepa"><x-icon name="home" /> Ubigeo - Depa. (opcional)</label>
                <input id="idDepa" type="text" wire:model="idDepa" maxlength="2" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 2)">
                @error('idDepa') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="idProv"><x-icon name="home" /> Ubigeo - Prov. (opcional)</label>
                <input id="idProv" type="text" wire:model="idProv" maxlength="2" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 2)">
                @error('idProv') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="idDist"><x-icon name="home" /> Ubigeo - Dist. (opcional)</label>
                <input id="idDist" type="text" wire:model="idDist" maxlength="2" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 2)">
                @error('idDist') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="buscar">
                        <span wire:loading.remove wire:target="buscar"><x-icon name="search" /> Buscar</span>
                        <span wire:loading.flex wire:target="buscar"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="buscar" aria-label="Limpiar consulta">
                        <x-icon name="eraser" /><span class="sr-only">Limpiar</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    @if($successMessage || $errorMessage)
        <div class="consulta-alert {{ $successMessage ? 'success' : ($real ? 'danger' : 'warning') }}" role="status">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @if($searched && $real && !empty($certificaciones))
        <section class="consulta-legacy-results" aria-live="polite">
            @foreach($certificaciones as $cert)
                <article class="glass consulta-info-card" style="width: 100%;">
                    <h2><x-icon name="leaf" /> {{ $cert['nombre_proyecto'] ?: 'Certificación ambiental' }}</h2>
                    <div class="consulta-info-grid">
                        <div class="consulta-info-item"><span>Expediente</span><strong>{{ $cert['expediente'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Estado</span><strong>{{ $cert['estado'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Tipo IGA</span><strong>{{ $cert['tipo_iga'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Ente evaluador</span><strong>{{ $cert['ente'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Sector</span><strong>{{ $cert['sector'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Subsector</span><strong>{{ $cert['subsector'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Actividad</span><strong>{{ $cert['actividad'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Titular</span><strong>{{ $cert['titular'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>RUC titular</span><strong>{{ $cert['ruc_titular'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Consultora</span><strong>{{ $cert['consultora'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>RUC consultora</span><strong>{{ $cert['ruc_consultora'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>N° resolución</span><strong>{{ $cert['nro_resol'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Fecha de resolución</span><strong>{{ $cert['fec_resol'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>Fecha de ingreso</span><strong>{{ $cert['fec_ingreso'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item"><span>N° catálogo</span><strong>{{ $cert['catalogo'] ?: '-' }}</strong></div>
                        <div class="consulta-info-item full"><span>Ubicación</span><strong>{{ collect([$cert['ubigeo']['departamento'] ?? null, $cert['ubigeo']['provincia'] ?? null, $cert['ubigeo']['distrito'] ?? null])->filter()->implode(' / ') ?: '-' }}</strong></div>
                    </div>

                    @if(!empty($cert['v_acceso']) || !empty($cert['v_lineaBase']))
                        <div class="consulta-info-links">
                            @foreach($cert['v_acceso'] as $enlace)
                                @if(is_string($enlace))
                                    <a href="{{ $enlace }}" target="_blank" rel="noopener noreferrer" class="consulta-button consulta-button-clear"><x-icon name="document" /> Acceso</a>
                                @endif
                            @endforeach
                            @foreach($cert['v_lineaBase'] as $enlace)
                                @if(is_string($enlace))
                                    <a href="{{ $enlace }}" target="_blank" rel="noopener noreferrer" class="consulta-button consulta-button-clear"><x-icon name="document" /> Línea base</a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    @endif
</div>
