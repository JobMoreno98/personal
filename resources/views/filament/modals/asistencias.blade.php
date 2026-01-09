<x-filament::section>
    <div
        class="fi-ta overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                            Fecha
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                            Equipo
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                            Método
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                            Foto
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($registros as $registro)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y H:i A') }}
                            </td>

                            <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                                {{ $registro->equipo }}
                            </td>

                            <td class="px-4 py-3">
                                <span
                                    class="
                                inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @switch($registro->tipo)
                                    @case('teclado') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 @break
                                    @case('huella') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @break
                                    @case('justificante') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 @break
                                    @default bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300
                                @endswitch
                            ">
                                    {{ ucfirst($registro->tipo) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                @if ($registro->tipo === 'teclado')
                                    <img src="{{ $registro->foto_url }}">
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                No hay registros de asistencia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::section>
