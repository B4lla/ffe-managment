<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Crear Empresa') }}
		</h2>
	</x-slot>

	<div class="py-12">
		<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
				@if ($errors->any())
					<div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
						<ul class="list-disc pl-5">
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<form method="POST" action="{{ route('empresas.store') }}" class="space-y-5">
					@csrf

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label for="nombre_razon_social" class="block text-sm font-medium text-gray-700">Nombre / Razón Social</label>
							<input id="nombre_razon_social" name="nombre_razon_social" type="text" value="{{ old('nombre_razon_social') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>

						<div>
							<label for="dni_cif" class="block text-sm font-medium text-gray-700">DNI / CIF</label>
							<input id="dni_cif" name="dni_cif" type="text" value="{{ old('dni_cif') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
							<input id="categoria" name="categoria" type="text" value="{{ old('categoria') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>

						<div>
							<label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
							<input id="tipo" name="tipo" type="text" value="{{ old('tipo') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label for="email" class="block text-sm font-medium text-gray-700">Email</label>
							<input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>

						<div>
							<label for="telefono1" class="block text-sm font-medium text-gray-700">Teléfono 1</label>
							<input id="telefono1" name="telefono1" type="text" value="{{ old('telefono1') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label for="telefono2" class="block text-sm font-medium text-gray-700">Teléfono 2</label>
							<input id="telefono2" name="telefono2" type="text" value="{{ old('telefono2') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>

						<div>
							<label for="provincia" class="block text-sm font-medium text-gray-700">Provincia</label>
							<input id="provincia" name="provincia" type="text" value="{{ old('provincia') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label for="municipio" class="block text-sm font-medium text-gray-700">Municipio</label>
							<input id="municipio" name="municipio" type="text" value="{{ old('municipio') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>

						<div>
							<label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código postal</label>
							<input id="codigo_postal" name="codigo_postal" type="text" value="{{ old('codigo_postal') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
						</div>
					</div>

					<div>
						<label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
						<textarea id="direccion" name="direccion" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('direccion') }}</textarea>
					</div>

					<div class="flex items-center justify-end gap-3 pt-3">
						<a href="{{ route('empresas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
							Cancelar
						</a>
						<button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
							Guardar empresa
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</x-app-layout>
