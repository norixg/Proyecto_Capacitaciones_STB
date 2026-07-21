<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Crear módulo para: {{ $capacitacion->capacitacion }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="mb-6 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                            <strong>Revisa los siguientes errores:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('capacitaciones.modulos.store', $capacitacion->id_capacitacion) }}">
                        @csrf

                        <input type="hidden" name="origen" value="builder">

                        <div class="mb-4">
                            <label class="block mb-1">Título del módulo</label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}"
                                class="w-full border rounded px-3 py-2 text-black @error('titulo') border-red-500 @enderror">
                            @error('titulo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Descripción</label>
                            <textarea name="descripcion" rows="4"
                                class="w-full border rounded px-3 py-2 text-black @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
                            @error('descripcion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Objetivo</label>
                            <textarea name="objetivo" rows="3"
                                class="w-full border rounded px-3 py-2 text-black @error('objetivo') border-red-500 @enderror">{{ old('objetivo') }}</textarea>
                            @error('objetivo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="font-bold text-gray-900">
                                        Teoría del módulo
                                    </h3>

                                    <p class="text-xs text-gray-600">
                                        Agrega secciones y subsecciones del módulo. Después de guardar el módulo, podrás agregar recursos, ejercicios y evaluaciones a cada subsección.
                                    </p>
                                </div>

                                <button type="button"
                                        onclick="agregarSeccionModulo('contenedorSeccionesModulo')"
                                        class="px-3 py-2 bg-blue-600 text-white rounded text-sm">
                                    + Agregar página
                                </button>
                            </div>

                            <div id="contenedorSeccionesModulo" class="space-y-4">
                                <div class="seccion-modulo-item rounded border border-gray-300 bg-white p-4"
                                    data-bloque-pagina-seccion>
                                    <input type="hidden" name="secciones_id[]" value="">
                                    <input type="hidden" name="secciones_padre[]" value="">

                                    <div class="flex items-center justify-between mb-3 gap-2">
                                        <strong>Página / sección</strong>

                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <button type="button"
                                                    onclick="agregarSubseccionModuloCrear(this)"
                                                    class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion">
                                                + Subsección
                                            </button>

                                            <button type="button"
                                                    onclick="eliminarSeccionModulo(this)"
                                                    class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                                Quitar
                                            </button>
                                        </div>
                                    </div>

                                    <label class="block text-sm font-medium" data-label-titulo-seccion-crear>
                                        Título de la página
                                    </label>

                                    <input type="text"
                                        name="secciones_titulo[]"
                                        placeholder="Ejemplo: Introducción"
                                        class="w-full border rounded px-3 py-2 text-black mb-3">

                                    <label class="block text-sm font-medium mb-1">
                                        Tipo de sección
                                    </label>

                                    <select name="secciones_nivel[]"
                                            onchange="actualizarSelectorNivelSeccionCrear(this)"
                                            class="w-full border rounded px-3 py-2 text-black mb-3">
                                        <option value="1" selected>Sección principal</option>
                                        <option value="2">Subsección</option>
                                    </select>

                                    <div class="rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 mb-3"
                                        data-aviso-tipo-seccion-crear>
                                        Tipo: Sección principal.
                                    </div>

                                    <label class="block text-sm font-medium mb-1">Contenido escrito</label>

                                    <input type="hidden"
                                        name="secciones_contenido[]"
                                        class="input-contenido-seccion-modulo">

                                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                                        style="min-height: 270px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block mb-1">Orden</label>
                                <input type="number" name="orden" value="{{ old('orden') }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('orden') border-red-500 @enderror">
                                @error('orden') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Duración en horas</label>
                                <input type="number" step="0.01" name="duracion_horas" value="{{ old('duracion_horas') }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('duracion_horas') border-red-500 @enderror">
                                @error('duracion_horas') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Requiere evaluación</label>
                                <select name="requiere_evaluacion"
                                    class="w-full border rounded px-3 py-2 text-black @error('requiere_evaluacion') border-red-500 @enderror">
                                    <option value="1" {{ old('requiere_evaluacion') == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('requiere_evaluacion', '0') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('requiere_evaluacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Porcentaje de aprobación</label>
                                <input type="number" step="0.01" name="porcentaje_aprobacion" value="{{ old('porcentaje_aprobacion') }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('porcentaje_aprobacion') border-red-500 @enderror">
                                @error('porcentaje_aprobacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1">Estado</label>
                                <select name="estado"
                                    class="w-full border rounded px-3 py-2 text-black @error('estado') border-red-500 @enderror">
                                    <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('estado') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar
                            </button>

                            <a href="{{ route('capacitaciones.builder', $capacitacion->id_capacitacion) }}" class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>

    <style>
        .editor-contenido-seccion-modulo .ql-editor {
            min-height: 220px;
            font-size: 16px;
            line-height: 1.75;
        }

        .editor-contenido-seccion-modulo .ql-editor img {
            max-width: 100%;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 12px 0;
        }

        .editor-contenido-seccion-modulo .ql-toolbar {
            border-radius: 0.375rem 0.375rem 0 0;
            background: #ffffff;
        }

        .editor-contenido-seccion-modulo .ql-container {
            border-radius: 0 0 0.375rem 0.375rem;
            background: #ffffff;
        }

        .ql-font-arial {
            font-family: Arial, sans-serif;
        }

        .ql-font-times-new-roman {
            font-family: "Times New Roman", serif;
        }

        .ql-font-georgia {
            font-family: Georgia, serif;
        }

        .ql-font-verdana {
            font-family: Verdana, sans-serif;
        }

        .ql-font-tahoma {
            font-family: Tahoma, sans-serif;
        }

        .ql-font-trebuchet-ms {
            font-family: "Trebuchet MS", sans-serif;
        }

        .ql-font-courier-new {
            font-family: "Courier New", monospace;
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before {
            content: "Arial";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="times-new-roman"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="times-new-roman"]::before {
            content: "Times";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before {
            content: "Georgia";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="verdana"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before {
            content: "Verdana";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="tahoma"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="tahoma"]::before {
            content: "Tahoma";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="trebuchet-ms"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="trebuchet-ms"]::before {
            content: "Trebuchet";
        }

        .ql-snow .ql-picker.ql-font .ql-picker-label[data-value="courier-new"]::before,
        .ql-snow .ql-picker.ql-font .ql-picker-item[data-value="courier-new"]::before {
            content: "Courier";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="12px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="12px"]::before {
            content: "12px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="14px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="14px"]::before {
            content: "14px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="16px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="16px"]::before {
            content: "16px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="18px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="18px"]::before {
            content: "18px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="20px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="20px"]::before {
            content: "20px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="24px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="24px"]::before {
            content: "24px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="28px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="28px"]::before {
            content: "28px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="32px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="32px"]::before {
            content: "32px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="36px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="36px"]::before {
            content: "36px";
        }

        .ql-snow .ql-picker.ql-size .ql-picker-label[data-value="48px"]::before,
        .ql-snow .ql-picker.ql-size .ql-picker-item[data-value="48px"]::before {
            content: "48px";
        }

        .editor-contenido-seccion-modulo .ql-editor {
            min-height: 220px;
            font-size: 15px;
            line-height: 1.7;
        }

        .editor-contenido-seccion-modulo .ql-editor [style*="column-count"] {
            column-gap: 24px;
        }

        .editor-contenido-seccion-modulo .ql-editor img {
            max-width: 100%;
            height: auto;
            cursor: pointer;
        }

        .ql-toolbar button.ql-columnas::before {
            content: "Cols";
            font-size: 10px;
            font-weight: 700;
        }

        .ql-toolbar button.ql-imageLeft::before {
            content: "Img←";
            font-size: 9px;
            font-weight: 700;
        }

        .ql-toolbar button.ql-imageInline::before {
            content: "Img↔";
            font-size: 9px;
            font-weight: 700;
        }

        .ql-toolbar button.ql-imageRight::before {
            content: "Img→";
            font-size: 9px;
            font-weight: 700;
        }

        .panel-ajuste-imagen-teoria {
            display: none;
            position: fixed;
            z-index: 99999;
            width: min(520px, calc(100vw - 2rem));
            padding: 0.85rem;
            border: 1px solid rgba(147, 197, 253, 0.95);
            border-radius: 18px;
            background: rgba(239, 246, 255, 0.98);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(14px);
        }

        .panel-ajuste-imagen-teoria.activo {
            display: block;
        }

        .panel-ajuste-imagen-teoria::before {
            content: "";
            position: absolute;
            top: -8px;
            left: 28px;
            width: 14px;
            height: 14px;
            transform: rotate(45deg);
            border-left: 1px solid rgba(147, 197, 253, 0.95);
            border-top: 1px solid rgba(147, 197, 253, 0.95);
            background: rgba(239, 246, 255, 0.98);
        }

        .panel-ajuste-imagen-teoria-titulo {
            font-size: 0.78rem;
            font-weight: 900;
            color: #1e3a8a;
            margin-bottom: 0.65rem;
        }

        .panel-ajuste-imagen-teoria-controles {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .panel-ajuste-imagen-teoria button {
            border: 1px solid rgba(191, 219, 254, 0.95);
            background: #ffffff;
            color: #1e3a8a;
            border-radius: 999px;
            padding: 0.42rem 0.68rem;
            font-size: 0.72rem;
            font-weight: 900;
            cursor: pointer;
        }

        .panel-ajuste-imagen-teoria button:hover {
            background: #dbeafe;
        }

        .panel-ajuste-imagen-teoria-ayuda {
            margin-top: 0.65rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
        }

        @media (max-width: 640px) {
            .panel-ajuste-imagen-teoria {
                left: 1rem !important;
                right: 1rem !important;
                top: auto !important;
                bottom: 1rem !important;
                width: auto;
                max-height: 45vh;
                overflow-y: auto;
            }

            .panel-ajuste-imagen-teoria::before {
                display: none;
            }

            .panel-ajuste-imagen-teoria button {
                flex: 1 1 auto;
            }
    }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const urlSubidaImagenTeoriaModulo = "{{ route('capacitacion_modulos.teoria.imagen') }}";
        const tokenCsrfTeoriaModulo = "{{ csrf_token() }}";

        const FontTeoriaModulo = Quill.import('formats/font');
        FontTeoriaModulo.whitelist = [
            'arial',
            'times-new-roman',
            'georgia',
            'verdana',
            'tahoma',
            'trebuchet-ms',
            'courier-new'
        ];
        Quill.register(FontTeoriaModulo, true);

        const SizeTeoriaModulo = Quill.import('attributors/style/size');
        SizeTeoriaModulo.whitelist = [
            '12px',
            '14px',
            '16px',
            '18px',
            '20px',
            '24px',
            '28px',
            '32px',
            '36px',
            '48px'
        ];
        Quill.register(SizeTeoriaModulo, true);

        if (window.ImageResize) {
            Quill.register('modules/imageResize', ImageResize);
        }

        const ParchmentTeoriaModulo = Quill.import('parchment');

        const ColumnasTeoriaModulo = new ParchmentTeoriaModulo.Attributor.Style(
            'columns',
            'column-count',
            {
                scope: ParchmentTeoriaModulo.Scope.BLOCK,
                whitelist: ['2', '3']
            }
        );

        const SeparacionColumnasTeoriaModulo = new ParchmentTeoriaModulo.Attributor.Style(
            'columnGap',
            'column-gap',
            {
                scope: ParchmentTeoriaModulo.Scope.BLOCK,
                whitelist: ['24px']
            }
        );

        Quill.register(ColumnasTeoriaModulo, true);
        Quill.register(SeparacionColumnasTeoriaModulo, true);

        const toolbarTeoriaModulo = [
            [{ 'font': [] }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'columnas'],
            ['imageLeft', 'imageInline', 'imageRight'],
            ['clean']
        ];

        function obtenerModulosQuillTeoria() {
            const modulos = {
                toolbar: toolbarTeoriaModulo,
                history: {
                    delay: 1000,
                    maxStack: 100,
                    userOnly: true
                }
            };

            if (window.ImageResize) {
                modulos.imageResize = {
                    modules: ['Resize', 'DisplaySize', 'Toolbar']
                };
            }

            return modulos;
        }

        function aplicarColumnasTeoriaModulo(quill) {
            const rango = quill.getSelection(true);

            if (!rango) {
                return;
            }

            const valor = prompt('Escribí 2 o 3 para poner columnas. Escribí 0 para quitar columnas.', '2');

            if (valor === null) {
                return;
            }

            const columnas = valor.trim();

            if (columnas === '0' || columnas === '') {
                quill.formatLine(rango.index, rango.length || 1, 'columns', false);
                quill.formatLine(rango.index, rango.length || 1, 'columnGap', false);
                return;
            }

            if (!['2', '3'].includes(columnas)) {
                alert('Solo se permite usar 2 o 3 columnas.');
                return;
            }

            quill.formatLine(rango.index, rango.length || 1, 'columns', columnas);
            quill.formatLine(rango.index, rango.length || 1, 'columnGap', '24px');
        }

        function subirImagenTeoriaModulo(archivo, quill) {
            const formData = new FormData();
            formData.append('imagen', archivo);

            fetch(urlSubidaImagenTeoriaModulo, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenCsrfTeoriaModulo,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.url) {
                    alert('No se pudo subir la imagen.');
                    return;
                }

                const rango = quill.getSelection(true);
                quill.insertEmbed(rango.index, 'image', data.url);
                quill.setSelection(rango.index + 1);
            })
            .catch(() => {
                alert('Ocurrió un error al subir la imagen.');
            });
        }

        function seleccionarImagenTeoriaModulo(quill) {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = function () {
                const archivo = input.files[0];

                if (archivo) {
                    subirImagenTeoriaModulo(archivo, quill);
                }
            };
        }

        function avisarImagenEditorTeoriaModulo(quill, mensaje) {
            if (typeof mostrarAvisoEditorModulo === 'function') {
                mostrarAvisoEditorModulo(quill, mensaje);
                return;
            }

            alert(mensaje);
        }

        function obtenerImagenSeleccionadaTeoriaModulo(quill, silencioso = false) {
            const editor = quill && quill.container ? quill.container : null;

            if (editor && editor.__imagenSeleccionadaTeoriaModulo) {
                return editor.__imagenSeleccionadaTeoriaModulo;
            }

            const rango = quill.getSelection(true);

            if (!rango) {
                if (!silencioso) {
                    avisarImagenEditorTeoriaModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
                }

                return null;
            }

            const leaf = quill.getLeaf(rango.index);

            if (!leaf || !leaf[0] || !leaf[0].domNode) {
                if (!silencioso) {
                    avisarImagenEditorTeoriaModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
                }

                return null;
            }

            const nodo = leaf[0].domNode;

            if (nodo.tagName === 'IMG') {
                return nodo;
            }

            if (nodo.previousSibling && nodo.previousSibling.tagName === 'IMG') {
                return nodo.previousSibling;
            }

            if (!silencioso) {
                avisarImagenEditorTeoriaModulo(quill, 'Primero haz clic sobre una imagen dentro del editor.');
            }

            return null;
        }

        function actualizarAtributosImagenTeoriaModulo(imagen, posicion, ancho) {
            if (!imagen) {
                return;
            }

            imagen.classList.add('imagen-teoria-ajustada');
            imagen.setAttribute('data-align', posicion);
            imagen.setAttribute('data-width', ancho);

            imagen.style.maxWidth = '100%';
            imagen.style.height = 'auto';
            imagen.style.cursor = 'pointer';

            if (ancho === 'auto') {
                imagen.style.width = '';
            } else {
                imagen.style.width = ancho;
            }

            if (posicion === 'left') {
                imagen.style.float = 'left';
                imagen.style.display = 'inline-block';
                imagen.style.margin = '0 18px 12px 0';
                imagen.style.verticalAlign = 'middle';
                return;
            }

            if (posicion === 'right') {
                imagen.style.float = 'right';
                imagen.style.display = 'inline-block';
                imagen.style.margin = '0 0 12px 18px';
                imagen.style.verticalAlign = 'middle';
                return;
            }

            if (posicion === 'center') {
                imagen.style.float = '';
                imagen.style.display = 'block';
                imagen.style.margin = '14px auto';
                imagen.style.verticalAlign = '';
                return;
            }

            imagen.style.float = '';
            imagen.style.display = 'block';
            imagen.style.margin = '14px 0';
            imagen.style.verticalAlign = '';
        }

        function aplicarAjusteImagenTeoriaModulo(editor, quill, posicion = null, ancho = null) {
            const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill);

            if (!imagen) {
                return;
            }

            const posicionActual = posicion || imagen.getAttribute('data-align') || 'normal';
            const anchoActual = ancho || imagen.getAttribute('data-width') || imagen.style.width || 'auto';

            actualizarAtributosImagenTeoriaModulo(imagen, posicionActual, anchoActual);

            marcarContenidoSeccionModuloTocado(editor);
            quill.update('silent');
            sincronizarEditorSeccionModulo(editor);
        }

        function limpiarAjusteImagenTeoriaModulo(editor, quill) {
            const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill);

            if (!imagen) {
                return;
            }

            imagen.classList.remove('imagen-teoria-ajustada');
            imagen.removeAttribute('data-align');
            imagen.removeAttribute('data-width');
            imagen.removeAttribute('style');

            imagen.style.maxWidth = '100%';
            imagen.style.height = 'auto';
            imagen.style.margin = '12px 0';
            imagen.style.cursor = 'pointer';

            marcarContenidoSeccionModuloTocado(editor);
            quill.update('silent');
            sincronizarEditorSeccionModulo(editor);
        }

        function aplicarPosicionImagenTeoriaModulo(quill, posicion) {
            const editor = quill && quill.container ? quill.container : null;

            if (!editor) {
                return;
            }

            aplicarAjusteImagenTeoriaModulo(editor, quill, posicion, null);
        }

        function cerrarPanelesAjusteImagenTeoriaModulo(panelPermitido = null) {
            document.querySelectorAll('.panel-ajuste-imagen-teoria.activo').forEach(function (panelAbierto) {
                if (panelPermitido && panelAbierto === panelPermitido) {
                    return;
                }

                panelAbierto.classList.remove('activo');
            });
        }

        function posicionarPanelAjusteImagenTeoriaModulo(panel, imagen) {
            if (!panel || !imagen) {
                return;
            }

            const rectImagen = imagen.getBoundingClientRect();

            panel.classList.add('activo');

            const anchoPanel = panel.offsetWidth || 520;
            const altoPanel = panel.offsetHeight || 120;
            const margen = 12;

            let top = rectImagen.bottom + margen;
            let left = rectImagen.left;

            if ((left + anchoPanel) > (window.innerWidth - margen)) {
                left = window.innerWidth - anchoPanel - margen;
            }

            if (left < margen) {
                left = margen;
            }

            if ((top + altoPanel) > (window.innerHeight - margen)) {
                top = rectImagen.top - altoPanel - margen;
            }

            if (top < margen) {
                top = margen;
            }

            panel.style.top = `${top}px`;
            panel.style.left = `${left}px`;
        }

        function mostrarPanelAjusteImagenTeoriaModulo(editor, quill, imagen) {
            if (!editor || !quill || !imagen) {
                return;
            }

            const panel = editor.__panelAjusteImagenTeoriaModulo;

            if (!panel) {
                return;
            }

            editor.__imagenSeleccionadaTeoriaModulo = imagen;

            cerrarPanelesAjusteImagenTeoriaModulo(panel);
            posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
        }

        function crearPanelAjusteImagenTeoriaModulo(editor, quill) {
            if (!editor || !quill) {
                return null;
            }

            if (editor.__panelAjusteImagenTeoriaModulo) {
                return editor.__panelAjusteImagenTeoriaModulo;
            }

            const panel = document.createElement('div');
            panel.className = 'panel-ajuste-imagen-teoria';
            panel.innerHTML = `
                <div class="panel-ajuste-imagen-teoria-titulo">
                    Ajustes de imagen
                </div>

                <div class="panel-ajuste-imagen-teoria-controles">
                    <button type="button" data-img-width="25%">Pequeña</button>
                    <button type="button" data-img-width="50%">Mediana</button>
                    <button type="button" data-img-width="75%">Grande</button>
                    <button type="button" data-img-width="100%">Completa</button>

                    <button type="button" data-img-align="left">Izquierda + texto</button>
                    <button type="button" data-img-align="center">Centrada</button>
                    <button type="button" data-img-align="right">Derecha + texto</button>
                    <button type="button" data-img-align="normal">Normal</button>

                    <button type="button" data-img-clear="1">Quitar ajustes</button>
                </div>

                <p class="panel-ajuste-imagen-teoria-ayuda">
                    Selecciona una imagen del contenido escrito para ajustar tamaño y posición.
                </p>
            `;

            document.body.appendChild(panel);
            editor.__panelAjusteImagenTeoriaModulo = panel;

            panel.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            panel.querySelectorAll('[data-img-width]').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    aplicarAjusteImagenTeoriaModulo(editor, quill, null, boton.dataset.imgWidth);

                    const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                    if (imagen) {
                        posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
                    }
                });
            });

            panel.querySelectorAll('[data-img-align]').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    aplicarAjusteImagenTeoriaModulo(editor, quill, boton.dataset.imgAlign, null);

                    const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                    if (imagen) {
                        posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
                    }
                });
            });

            const botonLimpiar = panel.querySelector('[data-img-clear]');

            if (botonLimpiar) {
                botonLimpiar.addEventListener('click', function () {
                    limpiarAjusteImagenTeoriaModulo(editor, quill);

                    const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                    if (imagen) {
                        posicionarPanelAjusteImagenTeoriaModulo(panel, imagen);
                    }
                });
            }

            return panel;
        }

        function obtenerModulosQuillTeoria() {
            const modulos = {
                toolbar: toolbarTeoriaModulo,
                history: {
                    delay: 1000,
                    maxStack: 100,
                    userOnly: true
                }
            };

            if (window.ImageResize) {
                modulos.imageResize = {
                    modules: ['Resize', 'DisplaySize', 'Toolbar']
                };
            }

            return modulos;
        }

        function subirImagenTeoriaModulo(archivo, quill) {
            const formData = new FormData();
            formData.append('imagen', archivo);

            fetch(urlSubidaImagenTeoriaModulo, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenCsrfTeoriaModulo,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.url) {
                    alert('No se pudo subir la imagen.');
                    return;
                }

                const rango = quill.getSelection(true);
                quill.insertEmbed(rango.index, 'image', data.url);
                quill.setSelection(rango.index + 1);
            })
            .catch(() => {
                alert('Ocurrió un error al subir la imagen.');
            });
        }

        function seleccionarImagenTeoriaModulo(quill) {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = function () {
                const archivo = input.files[0];

                if (archivo) {
                    subirImagenTeoriaModulo(archivo, quill);
                }
            };
        }

        function escapeHtmlModuloCrear(texto) {
            return String(texto ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function obtenerCampoContenidoSeccionModulo(item) {
            if (!item) {
                return null;
            }

            return item.querySelector('.input-contenido-seccion-modulo');
        }

        function obtenerCampoContenidoTocadoSeccionModulo(item) {
            if (!item) {
                return null;
            }

            return item.querySelector('.input-contenido-seccion-modulo-tocado');
        }

        function marcarContenidoSeccionModuloTocado(editor) {
            const item = editor ? editor.closest('.seccion-modulo-item') : null;
            const campoTocado = obtenerCampoContenidoTocadoSeccionModulo(item);

            if (campoTocado) {
                campoTocado.value = '1';
            }
        }

        function obtenerQuillDeEditorSeccionModulo(editor) {
            if (!editor || !window.Quill) {
                return null;
            }

            if (editor.__quill) {
                return editor.__quill;
            }

            try {
                const quillEncontrado = Quill.find(editor);

                if (quillEncontrado && quillEncontrado.root) {
                    editor.__quill = quillEncontrado;
                    return quillEncontrado;
                }
            } catch (error) {
                return null;
            }

            return null;
        }

        function normalizarHtmlContenidoSeccionModulo(html) {
            const contenido = String(html ?? '').trim();

            if (contenido === '' || contenido === '<p><br></p>') {
                return '';
            }

            return contenido;
        }

        function sincronizarEditorSeccionModulo(editor, forzarLecturaHtml = false) {
            if (!editor) {
                return;
            }

            const item = editor.closest('.seccion-modulo-item');
            const campoContenido = obtenerCampoContenidoSeccionModulo(item);

            if (!campoContenido) {
                return;
            }

            const quill = obtenerQuillDeEditorSeccionModulo(editor);

            if (quill && quill.root) {
                campoContenido.value = normalizarHtmlContenidoSeccionModulo(quill.root.innerHTML);
                return;
            }

            const editorInterno = editor.querySelector('.ql-editor');

            if (editorInterno) {
                campoContenido.value = normalizarHtmlContenidoSeccionModulo(editorInterno.innerHTML);
                return;
            }

            if (forzarLecturaHtml) {
                const htmlDirecto = normalizarHtmlContenidoSeccionModulo(editor.innerHTML);

                if (htmlDirecto !== '') {
                    campoContenido.value = htmlDirecto;
                }
            }
        }

        function configurarEventosEditorSeccionModulo(editor, quill) {
            if (!editor || !quill || editor.dataset.eventosQuillSeccion === '1') {
                return;
            }

            editor.dataset.eventosQuillSeccion = '1';

            const panelAjusteImagen = crearPanelAjusteImagenTeoriaModulo(editor, quill);

            quill.root.addEventListener('mouseover', function (event) {
                if (event.target && event.target.tagName === 'IMG') {
                    mostrarPanelAjusteImagenTeoriaModulo(editor, quill, event.target);
                }
            });

            quill.root.addEventListener('click', function (event) {
                if (event.target && event.target.tagName === 'IMG') {
                    mostrarPanelAjusteImagenTeoriaModulo(editor, quill, event.target);
                    return;
                }

                if (panelAjusteImagen && !panelAjusteImagen.contains(event.target)) {
                    panelAjusteImagen.classList.remove('activo');
                }
            });

            document.addEventListener('mousedown', function (event) {
                if (!panelAjusteImagen || !panelAjusteImagen.classList.contains('activo')) {
                    return;
                }

                if (panelAjusteImagen.contains(event.target)) {
                    return;
                }

                if (quill.root.contains(event.target) && event.target.tagName === 'IMG') {
                    return;
                }

                panelAjusteImagen.classList.remove('activo');
            });

            window.addEventListener('resize', function () {
                const imagen = obtenerImagenSeleccionadaTeoriaModulo(quill, true);

                if (panelAjusteImagen && panelAjusteImagen.classList.contains('activo') && imagen) {
                    posicionarPanelAjusteImagenTeoriaModulo(panelAjusteImagen, imagen);
                }
            });

            quill.on('text-change', function (delta, oldDelta, source) {
                sincronizarEditorSeccionModulo(editor);

                if (source === 'user') {
                    marcarContenidoSeccionModuloTocado(editor);
                }
            });

            quill.on('selection-change', function () {
                sincronizarEditorSeccionModulo(editor);
            });

            const toolbar = quill.getModule('toolbar');

            if (!toolbar) {
                return;
            }

            toolbar.addHandler('image', function () {
                marcarContenidoSeccionModuloTocado(editor);
                seleccionarImagenTeoriaModulo(quill);

                setTimeout(function () {
                    sincronizarEditorSeccionModulo(editor);
                }, 300);
            });

            toolbar.addHandler('columnas', function () {
                marcarContenidoSeccionModuloTocado(editor);
                aplicarColumnasTeoriaModulo(quill);
                sincronizarEditorSeccionModulo(editor);
            });

            toolbar.addHandler('imageLeft', function () {
                aplicarAjusteImagenTeoriaModulo(editor, quill, 'left', null);
            });

            toolbar.addHandler('imageInline', function () {
                aplicarAjusteImagenTeoriaModulo(editor, quill, 'center', null);
            });

            toolbar.addHandler('imageRight', function () {
                aplicarAjusteImagenTeoriaModulo(editor, quill, 'right', null);
            });

            quill.root.addEventListener('paste', function () {
                marcarContenidoSeccionModuloTocado(editor);

                setTimeout(function () {
                    sincronizarEditorSeccionModulo(editor);
                }, 100);
            });
        }

        function inicializarUnEditorSeccionModulo(editor) {
            if (!editor) {
                return;
            }

            const quillExistente = obtenerQuillDeEditorSeccionModulo(editor);

            if (quillExistente) {
                editor.dataset.quillInicializado = '1';
                quillExistente.enable(true);
                configurarEventosEditorSeccionModulo(editor, quillExistente);
                sincronizarEditorSeccionModulo(editor);
                return;
            }

            if (editor.dataset.quillInicializado === '1') {
                sincronizarEditorSeccionModulo(editor);
                return;
            }

            const item = editor.closest('.seccion-modulo-item');
            const campoContenido = obtenerCampoContenidoSeccionModulo(item);
            const campoTocado = obtenerCampoContenidoTocadoSeccionModulo(item);
            const contenidoInicial = campoContenido ? campoContenido.value : '';

            editor.innerHTML = '';

            const quill = new Quill(editor, {
                theme: 'snow',
                modules: obtenerModulosQuillTeoria()
            });

            editor.__quill = quill;
            editor.dataset.quillInicializado = '1';
            quill.enable(true);
            configurarEventosEditorSeccionModulo(editor, quill);

            if (contenidoInicial.trim() !== '') {
                quill.clipboard.dangerouslyPasteHTML(0, contenidoInicial);
            }

            sincronizarEditorSeccionModulo(editor);

            if (campoTocado) {
                campoTocado.value = '0';
            }
        }

        function inicializarEditoresSeccionesModulo() {
            document.querySelectorAll('.editor-contenido-seccion-modulo').forEach(function (editor) {
                try {
                    inicializarUnEditorSeccionModulo(editor);
                } catch (error) {
                    console.error('No se pudo inicializar una caja de contenido escrito:', error, editor);
                }
            });
        }

        function sincronizarEditoresSeccionesModulo() {
            document.querySelectorAll('.editor-contenido-seccion-modulo').forEach(function (editor) {
                sincronizarEditorSeccionModulo(editor, true);
            });
        }

        function agregarSeccionModulo(contenedorId) {
            const contenedor = document.getElementById(contenedorId);

            if (!contenedor) {
                return;
            }

            const html = `
                <div class="seccion-modulo-item rounded border border-gray-300 bg-white p-4"
                    data-bloque-pagina-seccion>
                    <input type="hidden" name="secciones_id[]" value="">
                    <input type="hidden" name="secciones_padre[]" value="">

                    <div class="flex items-center justify-between mb-3 gap-2">
                        <strong>Página / sección</strong>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button"
                                    onclick="agregarSubseccionModuloCrear(this)"
                                    class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion">
                                + Subsección
                            </button>

                            <button type="button"
                                    onclick="eliminarSeccionModulo(this)"
                                    class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                Quitar
                            </button>
                        </div>
                    </div>

                    <label class="block text-sm font-medium" data-label-titulo-seccion-crear>
                        Título de la página
                    </label>

                    <input type="text"
                        name="secciones_titulo[]"
                        placeholder="Ejemplo: Conceptos básicos"
                        class="w-full border rounded px-3 py-2 text-black mb-3">

                    <label class="block text-sm font-medium mb-1">
                        Tipo de sección
                    </label>

                    <select name="secciones_nivel[]"
                            onchange="actualizarSelectorNivelSeccionCrear(this)"
                            class="w-full border rounded px-3 py-2 text-black mb-3">
                        <option value="1" selected>Sección principal</option>
                        <option value="2">Subsección</option>
                    </select>

                    <div class="rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 mb-3"
                        data-aviso-tipo-seccion-crear>
                        Tipo: Sección principal.
                    </div>

                    <label class="block text-sm font-medium mb-1">Contenido escrito</label>

                    <input type="hidden"
                        name="secciones_contenido[]"
                        class="input-contenido-seccion-modulo">

                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                        style="min-height: 260px;"></div>
                </div>
            `;

            contenedor.insertAdjacentHTML('beforeend', html);
            inicializarEditoresSeccionesModulo();
        }

        function agregarSubseccionModuloCrear(boton) {
            const bloquePadre = boton.closest('.seccion-modulo-item');

            if (!bloquePadre) {
                return;
            }

            const inputTituloPadre = bloquePadre.querySelector('input[name="secciones_titulo[]"]');

            const tituloPadre = inputTituloPadre && inputTituloPadre.value.trim() !== ''
                ? inputTituloPadre.value.trim()
                : 'Sección principal';

            const html = `
                <div class="seccion-modulo-item rounded border border-emerald-300 bg-emerald-50 p-4 ml-6"
                    data-bloque-pagina-seccion>
                    <input type="hidden" name="secciones_id[]" value="">
                    <input type="hidden" name="secciones_padre[]" value="">

                    <div class="flex items-center justify-between mb-3 gap-2">
                        <strong>Subsección de: ${escapeHtmlModuloCrear(tituloPadre)}</strong>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button"
                                    onclick="agregarSubseccionModuloCrear(this)"
                                    class="px-2 py-1 bg-emerald-600 text-white rounded text-xs boton-agregar-subseccion hidden">
                                + Subsección
                            </button>

                            <button type="button"
                                    onclick="eliminarSeccionModulo(this)"
                                    class="px-2 py-1 bg-red-600 text-white rounded text-xs">
                                Quitar
                            </button>
                        </div>
                    </div>

                    <label class="block text-sm font-medium" data-label-titulo-seccion-crear>
                        Título de la subsección
                    </label>

                    <input type="text"
                        name="secciones_titulo[]"
                        placeholder="Ejemplo: Partes de un montacargas"
                        class="w-full border rounded px-3 py-2 text-black mb-3">

                    <label class="block text-sm font-medium mb-1">
                        Tipo de sección
                    </label>

                    <select name="secciones_nivel[]"
                            onchange="actualizarSelectorNivelSeccionCrear(this)"
                            class="w-full border rounded px-3 py-2 text-black mb-3">
                        <option value="1">Sección principal</option>
                        <option value="2" selected>Subsección</option>
                    </select>

                    <div class="rounded border border-yellow-300 bg-yellow-100 px-3 py-2 text-xs text-yellow-800 mb-3"
                        data-aviso-tipo-seccion-crear>
                        Guarda primero el módulo. Después podrás agregar recursos, ejercicios o evaluaciones a esta subsección.
                    </div>

                    <label class="block text-sm font-medium mb-1">Contenido escrito</label>

                    <input type="hidden"
                        name="secciones_contenido[]"
                        class="input-contenido-seccion-modulo">

                    <div class="editor-contenido-seccion-modulo bg-white text-black rounded border border-gray-300"
                        style="min-height: 260px;"></div>
                </div>
            `;

            const hijosExistentes = obtenerBloquesHijosSeccionCrear(bloquePadre);

            const referenciaInsercion = hijosExistentes.length > 0
                ? hijosExistentes[hijosExistentes.length - 1].nextElementSibling
                : bloquePadre.nextElementSibling;

            if (referenciaInsercion) {
                referenciaInsercion.insertAdjacentHTML('beforebegin', html);
            } else if (bloquePadre.parentElement) {
                bloquePadre.parentElement.insertAdjacentHTML('beforeend', html);
            }

            inicializarEditoresSeccionesModulo();
        }

        function obtenerNivelSeccionCrearDesdeBloque(bloque) {
            const selectNivel = bloque.querySelector('select[name="secciones_nivel[]"]');
            const inputNivel = bloque.querySelector('input[name="secciones_nivel[]"]');

            if (selectNivel) {
                return selectNivel.value;
            }

            if (inputNivel) {
                return inputNivel.value;
            }

            return '1';
        }

        function obtenerBloquesHijosSeccionCrear(bloquePadre) {
            const hijos = [];
            let siguiente = bloquePadre.nextElementSibling;

            while (siguiente && siguiente.classList.contains('seccion-modulo-item')) {
                if (obtenerNivelSeccionCrearDesdeBloque(siguiente) === '1') {
                    break;
                }

                hijos.push(siguiente);
                siguiente = siguiente.nextElementSibling;
            }

            return hijos;
        }

        function obtenerTituloSeccionPrincipalAnteriorCrear(bloque) {
            let anterior = bloque.previousElementSibling;

            while (anterior && anterior.classList.contains('seccion-modulo-item')) {
                if (obtenerNivelSeccionCrearDesdeBloque(anterior) === '1') {
                    const inputTitulo = anterior.querySelector('input[name="secciones_titulo[]"]');

                    return inputTitulo && inputTitulo.value.trim() !== ''
                        ? inputTitulo.value.trim()
                        : 'Sección principal';
                }

                anterior = anterior.previousElementSibling;
            }

            return 'Sección principal';
        }

        function actualizarSelectorNivelSeccionCrear(select) {
            const bloque = select.closest('[data-bloque-pagina-seccion]');

            if (!bloque) {
                return;
            }

            const nivelSeleccionado = select.value === '2' ? '2' : '1';

            const botonSubseccion = bloque.querySelector('.boton-agregar-subseccion');

            if (botonSubseccion) {
                botonSubseccion.classList.toggle('hidden', nivelSeleccionado !== '1');
            }

            const tituloEncabezado = bloque.querySelector(':scope > div.flex strong');

            if (tituloEncabezado) {
                if (nivelSeleccionado === '2') {
                    tituloEncabezado.textContent = 'Subsección de: ' + obtenerTituloSeccionPrincipalAnteriorCrear(bloque);
                } else {
                    tituloEncabezado.textContent = 'Página / sección';
                }
            }

            const labelTitulo = bloque.querySelector('[data-label-titulo-seccion-crear]');

            if (labelTitulo) {
                labelTitulo.textContent = nivelSeleccionado === '2'
                    ? 'Título de la subsección'
                    : 'Título de la página';
            }

            const avisoTipo = bloque.querySelector('[data-aviso-tipo-seccion-crear]');

            if (avisoTipo) {
                if (nivelSeleccionado === '2') {
                    avisoTipo.className = 'rounded border border-yellow-300 bg-yellow-100 px-3 py-2 text-xs text-yellow-800 mb-3';
                    avisoTipo.textContent = 'Guarda primero el módulo. Después podrás agregar recursos, ejercicios o evaluaciones a esta subsección.';
                } else {
                    avisoTipo.className = 'rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 mb-3';
                    avisoTipo.textContent = 'Tipo: Sección principal.';
                }
            }

            bloque.classList.toggle('ml-6', nivelSeleccionado === '2');

            bloque.classList.toggle('border-emerald-300', nivelSeleccionado === '2');
            bloque.classList.toggle('bg-emerald-50', nivelSeleccionado === '2');

            bloque.classList.toggle('border-gray-300', nivelSeleccionado !== '2');
            bloque.classList.toggle('bg-white', nivelSeleccionado !== '2');
        }

        function eliminarSeccionModulo(boton) {
            const item = boton.closest('.seccion-modulo-item');

            if (!item) {
                return;
            }

            const hijos = obtenerNivelSeccionCrearDesdeBloque(item) === '1'
                ? obtenerBloquesHijosSeccionCrear(item)
                : [];

            const cantidadTotal = hijos.length + 1;

            const confirmar = confirm(
                cantidadTotal > 1
                    ? 'Vas a quitar esta sección y sus subsecciones. ¿Deseas continuar?'
                    : 'Vas a quitar esta sección. ¿Deseas continuar?'
            );

            if (!confirmar) {
                return;
            }

            hijos.forEach(function (hijo) {
                hijo.remove();
            });

            item.remove();
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('submit', function () {
                sincronizarEditoresSeccionesModulo();
            }, true);

            try {
                inicializarEditoresSeccionesModulo();
            } catch (error) {
                console.error('Error al inicializar los editores de teoría del módulo:', error);
            }
        });
    </script>
</x-app-layout>