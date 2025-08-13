import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

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

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    history?: HistoryItem[];
    onHistoryClick?: (item: HistoryItem) => void;
    onNewSearch?: () => void;
}

export default ({ children, breadcrumbs, history, onHistoryClick, onNewSearch, ...props }: AppLayoutProps) => (
    <AppLayoutTemplate
        breadcrumbs={breadcrumbs}
        history={history}
        onHistoryClick={onHistoryClick}
        onNewSearch={onNewSearch}
        {...props}
    >
        {children}
    </AppLayoutTemplate>
);
