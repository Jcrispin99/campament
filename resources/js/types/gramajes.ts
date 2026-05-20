export type EstatusGramaje = 'CONFORME' | 'INCONFORME';
export type UnidadGramaje = 'GRAMOS' | 'UNIDADES';

export type UnidadOption = {
    value: UnidadGramaje;
    label: string;
};

export type CatalogoItem = {
    id: number;
    nombre: string;
};

export type ComponenteOption = CatalogoItem & {
    gramaje_sugerido: string;
    unidad: UnidadGramaje;
    observacion: string | null;
};

export type GramajeCatalogos = {
    comedores: CatalogoItem[];
    servicios: CatalogoItem[];
    platos: CatalogoItem[];
    componentes: ComponenteOption[];
    tiposCorte: CatalogoItem[];
    unidades: UnidadOption[];
};

export type GramajeMedida = {
    id: number;
    gramaje_id: number;
    peso: string;
    orden: number;
};

export type GramajeListItem = {
    id: number;
    fecha: string;
    semana: number;
    fecha_produccion: string;
    gramaje_esperado: string;
    cantidad_muestreada: number;
    peso_promedio: string;
    variacion_pct: string;
    estatus: EstatusGramaje;
    comedor: CatalogoItem | null;
    servicio: CatalogoItem | null;
    plato: CatalogoItem | null;
    componente: (CatalogoItem & { unidad: UnidadGramaje }) | null;
    tipo_corte: CatalogoItem | null;
    reportado_por: { id: number; name: string } | null;
};

export type Gramaje = GramajeListItem & {
    comedor_id: number;
    servicio_id: number;
    plato_id: number;
    componente_id: number;
    tipo_corte_id: number | null;
    reportado_por_id: number;
    medidas: GramajeMedida[];
};

export type PaginatedGramajes = {
    data: GramajeListItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
