import type { UnidadGramaje } from '@/types/gramajes';

export type CatalogoItem = {
    id: number;
    nombre: string;
};

export type MenuCatalogos = {
    servicios: CatalogoItem[];
    componentes: (CatalogoItem & { unidad: UnidadGramaje })[];
};

export type MenuListItem = {
    id: number;
    fecha: string;
    semana: number;
    fecha_solicitud: string;
    fecha_cambio: string;
    dias_prevision: number;
    conformidad: string;
    programado: string;
    propuesta: string;
    servicio: CatalogoItem | null;
    componente: (CatalogoItem & { unidad: UnidadGramaje }) | null;
    reportado_por: { id: number; name: string } | null;
};

export type Menu = MenuListItem & {
    servicio_id: number;
    componente_id: number;
    motivo: string;
    comentario: string | null;
    analisis: string;
    reportado_por_id: number;
};

export type PaginatedMenus = {
    data: MenuListItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
