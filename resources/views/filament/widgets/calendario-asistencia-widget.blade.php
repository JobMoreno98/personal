<x-filament-widgets::widget>
    <x-filament::section>

        {{-- Cabecera con Formulario y Botones de Exportación --}}
        <div class="flex flex-col md:flex-row items-start md:items-end justify-between mb-6 gap-4">
            <div style="    display: flex;
    justify-content: flex-end;">
                {{-- Renderizamos las acciones usando la etiqueta nativa de Filament --}}
                <x-filament-actions::modals />

                <span style="margin:0px 10px">
                    {{ $this->exportarAsistenciasAction() }}
                </span>
                <span style="margin:0px 10px">
                    {{ $this->exportarFaltasAction() }}
                </span>
            </div>
            {{-- Formulario de fechas (ocupa el espacio disponible) --}}
            <div class="flex-1 w-full">
                {{ $this->form }}
            </div>

            {{-- Botones de Acción (Se alinean a la derecha y abajo) --}}

        </div>
        @if (isset($error))
            <div
                style="padding: 1rem; margin-bottom: 1rem; color: #721c24; background-color: #f8d7da; border-radius: 0.5rem; border: 1px solid #f5c6cb;">
                <strong>Atención:</strong> {{ $error }}
            </div>
        @else
            <style>
                .calendar-grid {
                    display: grid;
                    grid-template-columns: repeat(7, 1fr);
                    gap: 8px;
                    width: 100%;
                    margin-top: 15px;
                }

                .day-header {
                    font-weight: bold;
                    padding: 8px 0;
                    background-color: rgba(128, 128, 128, 0.1);
                    text-align: center;
                    border-radius: 4px;
                }

                .day-box {
                    border: 1px solid rgba(128, 128, 128, 0.2);
                    border-radius: 6px;
                    padding: 8px;
                    min-height: 90px;
                    display: flex;
                    flex-direction: column;
                    transition: all 0.2s ease-in-out;
                }

                .day-number {
                    font-size: 1.1rem;
                    font-weight: bold;
                    margin-bottom: 4px;
                }

                .status-badge {
                    font-size: 0.65rem;
                    font-weight: bold;
                    color: white;
                    padding: 2px 4px;
                    border-radius: 3px;
                    text-align: center;
                    margin-top: auto;
                    line-height: 1.2;
                }

                .time-info {
                    font-size: 0.7rem;
                    margin-top: 4px;
                    line-height: 1.2;
                }

                .dark .day-box {
                    border-color: rgba(255, 255, 255, 0.1);
                }

                .dark .day-header {
                    background-color: rgba(255, 255, 255, 0.05);
                    color: #ccc;
                }
            </style>

            <div class="calendar-grid">
                {{-- Encabezados --}}
                {{-- Encabezados (Semana empezando en Lunes) --}}
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)
                    <div class="day-header">{{ $dia }}</div>
                @endforeach

                {{-- Espacios vacíos --}}
                @php
                    if (!empty($calendario)) {
                        $primerItem = $calendario[0];
                        $diaSemanaCarbon = \Carbon\Carbon::parse($primerItem['fecha_str'])->dayOfWeek;

                        // Ajuste matemático para semana en Lunes:
                        // Lunes=0, Martes=1... Domingo=6
                        $espaciosVacios = ($diaSemanaCarbon + 6) % 7;
                    } else {
                        $espaciosVacios = 0;
                    }
                @endphp

                @for ($i = 0; $i < $espaciosVacios; $i++)
                    <div></div>
                @endfor

                {{-- Días --}}
                @foreach ($calendario as $item)
                    <div class="day-box"
                        style="background-color: {{ $item['estado'] === 'DESCANSO' ? 'transparent' : 'rgba(128,128,128,0.03)' }};">

                        <div class="day-number"
                            style="color: {{ $item['color'] === '#f3f4f6' || $item['color'] === 'transparent' ? 'inherit' : $item['color'] }}">
                            {{ $item['dia'] }} <span
                                style="font-size: 0.65rem; font-weight: normal; opacity: 0.7;">{{ ucfirst(\Carbon\Carbon::parse($item['fecha_str'])->locale('es')->isoFormat('MMM')) }}</span>
                        </div>

                        @if ($item['detalle'])
                            <div class="status-badge" style="background-color: {{ $item['color'] }}">
                                {{ $item['estado'] }}
                            </div>
                            <div class="time-info">
                                <strong style="color: {{ $item['color'] }}">E:</strong>
                                {{ $item['detalle']['entrada'] }}<br>
                                <strong style="color: {{ $item['color'] }}">S:</strong>
                                {{ $item['detalle']['salida'] }}
                            </div>
                        @else
                            @if ($item['estado'] !== 'DESCANSO' && $item['estado'] !== 'PENDIENTE')
                                <div class="status-badge" style="background-color: {{ $item['color'] }}">
                                    {{ $item['estado'] }}
                                </div>
                            @elseif ($item['estado'] === 'DESCANSO')
                                <div style="font-size: 0.7rem; color: #888; margin-top: auto;">Descanso</div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
