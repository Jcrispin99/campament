const pad = (n: number): string => String(n).padStart(2, '0');

export function parseFechaLocal(iso: string): Date {
    const [y, m, d] = iso.slice(0, 10).split('-').map(Number);
    return new Date(y, (m ?? 1) - 1, d ?? 1);
}

export function formatFechaLocal(
    iso: string,
    options: Intl.DateTimeFormatOptions = { year: 'numeric', month: '2-digit', day: '2-digit' },
    locale = 'es-PE',
): string {
    return parseFechaLocal(iso).toLocaleDateString(locale, options);
}

export function todayLocalISO(): string {
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

export function toLocalISO(d: Date): string {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}
