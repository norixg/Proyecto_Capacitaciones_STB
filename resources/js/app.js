import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('selectAutocomplete', (opciones, idInicial = '', nombreCampo = '') => ({
    opciones,
    nombreCampo,
    consulta: '',
    idSeleccionado: idInicial ? String(idInicial) : '',
    abierto: false,
    indiceActivo: -1,

    init() {
        const seleccionado = this.opciones.find(
            (opcion) => String(opcion.id) === this.idSeleccionado,
        );

        this.consulta = seleccionado?.etiqueta ?? '';

        this.$nextTick(() => {
            this.$dispatch('autocomplete-selected', {
                name: this.nombreCampo,
                option: seleccionado ?? null,
            });
        });
    },

    normalizar(valor) {
        return String(valor ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    },

    get coincidencias() {
        const busqueda = this.normalizar(this.consulta);
        const resultados = busqueda
            ? this.opciones.filter((opcion) => opcion.busqueda.includes(busqueda))
            : this.opciones;

        return resultados.slice(0, 50);
    },

    escribir() {
        this.idSeleccionado = '';
        this.abierto = true;
        this.indiceActivo = this.coincidencias.length ? 0 : -1;
    },

    seleccionar(opcion) {
        this.idSeleccionado = String(opcion.id);
        this.consulta = opcion.etiqueta;
        this.abierto = false;
        this.indiceActivo = -1;
        this.$dispatch('autocomplete-selected', {
            name: this.nombreCampo,
            option: opcion,
        });
    },

    mover(direccion) {
        this.abierto = true;

        if (!this.coincidencias.length) {
            this.indiceActivo = -1;
            return;
        }

        this.indiceActivo = (this.indiceActivo + direccion + this.coincidencias.length)
            % this.coincidencias.length;
    },

    seleccionarActivo() {
        const opcion = this.coincidencias[this.indiceActivo];

        if (opcion) {
            this.seleccionar(opcion);
        }
    },

    limpiar() {
        this.consulta = '';
        this.idSeleccionado = '';
        this.abierto = true;
        this.indiceActivo = 0;
        this.$dispatch('autocomplete-selected', {
            name: this.nombreCampo,
            option: null,
        });
        this.$nextTick(() => this.$refs.entrada.focus());
    },
}));

Alpine.start();
