<form action="{{ $action }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
@csrf
@isset($method) @method($method) @endisset

<div class="grid gap-5 sm:grid-cols-1"><div><label for="nombre" class="mb-2 block text-sm font-medium">Nombre</label>
<input id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre ?? '') }}" required maxlength="100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" @error('nombre') aria-invalid="true" aria-describedby="nombre-error" @enderror>
@error('nombre')<p id="nombre-error" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>@enderror</div></div>
<div class="mt-8 flex justify-end gap-3"><a href="{{ route('admin.categorias.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm dark:border-slate-700">Cancelar</a><button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus-visible:outline-blue-400" >Guardar categoría</button></div></form>