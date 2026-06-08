<x-filament-panels::page ca>

    <div style="display: flex    ; justify-content: space-around;">

        <!-- IZQUIERDA -->
        <div style="width:38%">
            @livewire(\App\Livewire\EmpleadosWidget::class)
        </div>

        <!-- DERECHA -->
        <div style="width:60%">
            @livewire(\App\Livewire\RegistrosWidget::class)
        </div>

    </div>

</x-filament-panels::page>
