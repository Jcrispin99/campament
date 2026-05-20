<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Beef,
    BookOpen,
    Boxes,
    Building2,
    CalendarSync,
    ChevronRight,
    ClipboardList,
    FileSearch,
    FolderGit2,
    Gauge,
    Layers,
    LayoutGrid,
    List,
    MapPin,
    Package,
    Salad,
    Scale,
    Scissors,
    Truck,
    UtensilsCrossed,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { index as analisisCausasIndex } from '@/routes/catalogos/analisis-causas';
import { index as clasificacionesIndex } from '@/routes/catalogos/clasificaciones';
import { index as comedoresIndex } from '@/routes/catalogos/comedores';
import { index as componentesIndex } from '@/routes/catalogos/componentes';
import { index as origenesIndex } from '@/routes/catalogos/origenes';
import { index as platosIndex } from '@/routes/catalogos/platos';
import { index as proveedoresIndex } from '@/routes/catalogos/proveedores';
import { index as serviciosIndex } from '@/routes/catalogos/servicios';
import { index as tiposCorteIndex } from '@/routes/catalogos/tipos-corte';
import { index as tiposIncidenteIndex } from '@/routes/catalogos/tipos-incidente';
import { index as tiposProductoIndex } from '@/routes/catalogos/tipos-producto';
import {
    dashboard as gramajesDashboard,
    index as gramajesIndex,
} from '@/routes/gramajes';
import {
    dashboard as materiasPrimasDashboard,
    index as materiasPrimasIndex,
} from '@/routes/materias-primas';
import {
    dashboard as menusDashboard,
    index as menusIndex,
} from '@/routes/menus';
import {
    dashboard as reportesDashboard,
    index as reportesIndex,
} from '@/routes/reportes';
import type { NavItem } from '@/types';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();

const dashboardUrl = computed(() =>
    page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : '/',
);

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
    },
]);

const reportesItems = computed(() => [
    {
        title: 'Lista',
        href: reportesIndex().url,
        icon: List,
    },
    {
        title: 'Dashboard',
        href: reportesDashboard().url,
        icon: BarChart3,
    },
]);

const reportesIsOpen = computed(() =>
    reportesItems.value.some((item) => isCurrentUrl(item.href)),
);

const indicadoresSsppItems = computed(() => [
    {
        title: 'Gramajes',
        href: gramajesIndex().url,
        icon: Scale,
    },
    {
        title: 'Dashboard gramajes',
        href: gramajesDashboard().url,
        icon: BarChart3,
    },
    {
        title: 'Menú',
        href: menusIndex().url,
        icon: CalendarSync,
    },
    {
        title: 'Dashboard menú',
        href: menusDashboard().url,
        icon: BarChart3,
    },
    {
        title: 'Materia prima',
        href: materiasPrimasIndex().url,
        icon: Boxes,
    },
    {
        title: 'Dashboard MP',
        href: materiasPrimasDashboard().url,
        icon: BarChart3,
    },
]);

const indicadoresSsppIsOpen = computed(() =>
    indicadoresSsppItems.value.some((item) => isCurrentUrl(item.href)),
);

const catalogosItems = computed(() => [
    { title: 'Comedores', href: comedoresIndex().url, icon: Building2 },
    { title: 'Servicios', href: serviciosIndex().url, icon: UtensilsCrossed },
    { title: 'Tipos de incidente', href: tiposIncidenteIndex().url, icon: Layers },
    { title: 'Clasificaciones', href: clasificacionesIndex().url, icon: List },
    { title: 'Análisis de causa', href: analisisCausasIndex().url, icon: FileSearch },
    { title: 'Platos', href: platosIndex().url, icon: Salad },
    { title: 'Componentes', href: componentesIndex().url, icon: Beef },
    { title: 'Tipos de corte', href: tiposCorteIndex().url, icon: Scissors },
    { title: 'Tipos de producto', href: tiposProductoIndex().url, icon: Package },
    { title: 'Proveedores', href: proveedoresIndex().url, icon: Truck },
    { title: 'Orígenes', href: origenesIndex().url, icon: MapPin },
]);

const catalogosIsOpen = computed(() =>
    catalogosItems.value.some((item) => isCurrentUrl(item.href)),
);

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Reportes</SidebarGroupLabel>
                <SidebarMenu>
                    <Collapsible
                        :default-open="reportesIsOpen"
                        class="group/collapsible"
                    >
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton tooltip="Reportes">
                                    <ClipboardList />
                                    <span>Reportes</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                    />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem
                                        v-for="sub in reportesItems"
                                        :key="sub.title"
                                    >
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="isCurrentUrl(sub.href)"
                                        >
                                            <Link :href="sub.href">
                                                <component :is="sub.icon" />
                                                <span>{{ sub.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Indicadores SSPP</SidebarGroupLabel>
                <SidebarMenu>
                    <Collapsible
                        :default-open="indicadoresSsppIsOpen"
                        class="group/collapsible"
                    >
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton tooltip="Indicadores SSPP">
                                    <Gauge />
                                    <span>Indicadores SSPP</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                    />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem
                                        v-for="sub in indicadoresSsppItems"
                                        :key="sub.title"
                                    >
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="isCurrentUrl(sub.href)"
                                        >
                                            <Link :href="sub.href">
                                                <component :is="sub.icon" />
                                                <span>{{ sub.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Catálogos</SidebarGroupLabel>
                <SidebarMenu>
                    <Collapsible
                        :default-open="catalogosIsOpen"
                        class="group/collapsible"
                    >
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton tooltip="Catálogos">
                                    <Layers />
                                    <span>Catálogos</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                    />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem
                                        v-for="sub in catalogosItems"
                                        :key="sub.title"
                                    >
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="isCurrentUrl(sub.href)"
                                        >
                                            <Link :href="sub.href">
                                                <component :is="sub.icon" />
                                                <span>{{ sub.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
