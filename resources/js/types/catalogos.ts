export type CatalogoSimpleItem = {
    id: number;
    nombre: string;
    activo: boolean;
};

export type TipoIncidenteItem = {
    id: number;
    nombre: string;
    clasificaciones_count: number;
};

export type ClasificacionItem = {
    id: number;
    tipo_incidente_id: number;
    nombre: string;
    activo: boolean;
    tipo_incidente: { id: number; nombre: string } | null;
};

export type UnidadGramaje = 'GRAMOS' | 'UNIDADES';

export type ComponenteItem = {
    id: number;
    nombre: string;
    gramaje_sugerido: string;
    unidad: UnidadGramaje;
    observacion: string | null;
    activo: boolean;
};

export type UnidadOption = { value: UnidadGramaje; label: string };
