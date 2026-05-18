export type Criticidad = 'LEVE' | 'MODERADO' | 'CRITICO';

export type CriticidadOption = {
    value: Criticidad;
    label: string;
};

export type CatalogoItem = {
    id: number;
    nombre: string;
};

export type Clasificacion = CatalogoItem & {
    tipo_incidente_id: number;
};

export type TipoIncidenteWithClasificaciones = CatalogoItem & {
    clasificaciones: Clasificacion[];
};

export type ReporteCatalogos = {
    comedores: CatalogoItem[];
    servicios: CatalogoItem[];
    tiposIncidente: TipoIncidenteWithClasificaciones[];
    analisisCausas: CatalogoItem[];
    criticidades: CriticidadOption[];
};

export type Evidencia = {
    id: number;
    reporte_id: number;
    imagen: string;
    imagen_url?: string;
    descripcion: string;
};

export type ReporteListItem = {
    id: number;
    fecha: string;
    semana: number;
    criticidad: Criticidad;
    se_corrigio: boolean;
    requiere_plan_accion: boolean;
    comedor: CatalogoItem | null;
    servicio: CatalogoItem | null;
    tipo_incidente: CatalogoItem | null;
    clasificacion: (CatalogoItem & { tipo_incidente_id: number }) | null;
    analisis_causa: CatalogoItem | null;
    reportado_por: { id: number; name: string } | null;
};

export type Reporte = ReporteListItem & {
    detalle_observacion: string;
    accion_inmediata: string;
    recomendacion_salus: string;
    comedor_id: number;
    servicio_id: number;
    tipo_incidente_id: number;
    clasificacion_id: number;
    analisis_causa_id: number;
    reportado_por_id: number;
    evidencias: Evidencia[];
};

export type PaginatedReportes = {
    data: ReporteListItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
