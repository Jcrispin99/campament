export type ConformidadMp = 'CONFORME' | 'NO_CONFORME';

export type ConformidadOption = {
    value: ConformidadMp;
    label: string;
};

export type CatalogoItem = {
    id: number;
    nombre: string;
};

export type MateriaPrimaCatalogos = {
    tiposProducto: CatalogoItem[];
    proveedores: CatalogoItem[];
    origenes: CatalogoItem[];
    conformidades: ConformidadOption[];
};

export type MateriaPrimaListItem = {
    id: number;
    fecha: string;
    semana: number;
    conformidad_mp: ConformidadMp;
    conformidad_documentacion: ConformidadMp;
    conformidad_vehiculo: ConformidadMp;
    tipo_producto: CatalogoItem | null;
    proveedor: CatalogoItem | null;
    origen: CatalogoItem | null;
    reportado_por: { id: number; name: string } | null;
};

export type MateriaPrima = MateriaPrimaListItem & {
    tipo_producto_id: number;
    proveedor_id: number;
    origen_id: number;
    causa_nc_observacion: string;
    productos_afectados: string;
    accion_realizada: string;
    reportado_por_id: number;
};

export type PaginatedMateriasPrimas = {
    data: MateriaPrimaListItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
