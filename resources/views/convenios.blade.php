<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Convenios') }}
		</h2>
	</x-slot>

	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
				<form method="GET" action="{{ route('convenios.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
					<div>
						<label for="q" class="block text-sm font-medium text-gray-700">Buscar</label>
						<input
							type="text"
							id="q"
							name="q"
							value="{{ $filtros['q'] ?? '' }}"
							placeholder="Nombre, CIF, email, telefono..."
							class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
						>
					</div>

					<div>
						<label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
						<select id="categoria" name="categoria" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
							<option value="">Todas</option>
							@foreach($categorias as $value => $label)
								<option value="{{ $value }}" @selected(($filtros['categoria'] ?? '') == $value)>
									{{ $label }}
								</option>
							@endforeach
						</select>
					</div>

					<div>
						<label for="vigencia" class="block text-sm font-medium text-gray-700">Vigencia</label>
						<select id="vigencia" name="vigencia" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
							<option value="">Todas</option>
							@foreach($vigencias as $value => $label)
								<option value="{{ $value }}" @selected(($filtros['vigencia'] ?? '') == $value)>
									{{ $label }}
								</option>
							@endforeach
						</select>
					</div>

					<div>
						<label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
						<select id="tipo" name="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
							<option value="">Todos</option>
							@foreach($tipos as $value => $label)
								<option value="{{ $value }}" @selected(($filtros['tipo'] ?? '') == $value)>
									{{ $label }}
								</option>
							@endforeach
						</select>
					</div>

					<div class="flex items-end gap-2">
						<button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
							Filtrar
						</button>
						<a href="{{ route('convenios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
							Limpiar
						</a>
					</div>
				</form>

				@if (session('status'))
					<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
						{{ session('status') }}
					</div>
				@endif

				<div class="flex justify-end mb-4">
					@if($puede_crear)
						<a href="{{ route('convenios.insertar') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-black border-2 border-black rounded-md hover:bg-indigo-700">
							Crear convenio
						</a>
						<form action="{{ route('convenios.importar') }}" method="POST" enctype="multipart/form-data" class="inline-flex items-center ml-4">
							@csrf
							<input type="file" name="csv_file" accept=".csv,.xlsx" required class="mr-2">
							<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Importar Excel/CSV</button>
						</form>
					@endif
				</div>

				<div class="mb-4 text-sm text-gray-600">
					Mostrando {{ $convenios->count() }} de {{ $convenios->total() }} convenios
				</div>

				<div class="overflow-x-auto">
					<table class="min-w-full divide-y divide-gray-200">
						<thead class="bg-gray-50">
							<tr>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Empresa</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">DNI/CIF</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Telefono</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ubicacion</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha firma</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Caduca</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Vigencia</th>
								<th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ver</th>
							</tr>
						</thead>
						<tbody class="bg-white divide-y divide-gray-100">
							@forelse($convenios as $convenio)
								<tr>
									<td class="px-4 py-3 text-sm text-gray-900">{{ $convenio->empresa->nombre_razon_social }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ $convenio->empresa->dni_cif }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ $convenio->empresa->categoria ?? '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ $convenio->empresa->tipo ?? '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ $convenio->empresa->email ?? '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ $convenio->empresa->telefono1 ?? $convenio->empresa->telefono2 ?? '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ trim(($convenio->empresa->municipio ?? '').' '.($convenio->empresa->provincia ?? '')) ?: '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ optional($convenio->fecha_firma)->format('d/m/Y') ?? '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">{{ optional($convenio->fecha_caducidad)->format('d/m/Y') ?? '-' }}</td>
									<td class="px-4 py-3 text-sm text-gray-700">
										<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ \App\Models\Convenio::vigenciaBadgeClass($convenio->vigencia_key) }}">
											{{ \App\Models\Convenio::vigenciaLabel($convenio->vigencia_key) }}
										</span>
									</td>
									<td class="px-4 py-3 text-sm text-indigo-600">
										<a href="{{ route('convenios.show', $convenio->id) }}" class="hover:underline">Ver</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="11" class="px-4 py-8 text-center text-sm text-gray-500">
										No hay convenios para los filtros seleccionados.
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="mt-6">
					{{ $convenios->links() }}
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
