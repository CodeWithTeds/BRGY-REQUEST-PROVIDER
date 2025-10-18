import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface AvailabilityWindow {
    date: string; // YYYY-MM-DD in local timezone
    start_time: string; // HH:mm
    end_time: string; // HH:mm
    slot_interval_minutes: number;
    capacity_per_slot: number;
    is_active: boolean;
    remarks?: string;
}

export interface AvailabilityResponse {
    occupied: string[]; // HH:mm entries at capacity or full
    counts?: Record<string, number>; // HH:mm -> scheduled count
    capacity?: number; // per-slot capacity
    totalScheduled?: number; // total scheduled for that date
    remainingPerSlot?: Record<string, number>; // HH:mm -> remaining capacity
}
