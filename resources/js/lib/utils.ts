import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}


export function formatDisplayDate(val: any) {
    if (!val) return '';

    if (typeof val === 'string' && /^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
        return val;
    }

    const d = val instanceof Date ? val : new Date(val);
    if (Number.isNaN(d.getTime())) return String(val);

    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    return `${day}/${month}/${year}`;
}
