import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

interface HistoryItem {
  searchType: 'matricula' | 'solar_manzana' | 'parcela_dc';
  params: {
    matricula?: string;
    solar?: string;
    manzana?: string;
    parcela?: string;
    dc?: string;
  };
  timestamp: string;
  user: {
    name: string;
  };
}

interface AppSidebarLayoutProps {
    breadcrumbs?: BreadcrumbItem[];
    history?: HistoryItem[];
    onHistoryClick?: (item: HistoryItem) => void;
    onNewSearch?: () => void;
}

export default function AppSidebarLayout({ children, breadcrumbs = [], history, onHistoryClick, onNewSearch }: PropsWithChildren<AppSidebarLayoutProps>) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar history={history} onHistoryClick={onHistoryClick} onNewSearch={onNewSearch} />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
