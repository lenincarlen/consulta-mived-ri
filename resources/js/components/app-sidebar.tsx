import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Button } from '@/components/ui/button';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Barcode, BookOpen, Folder, Headphones, HelpCircle, LayoutGrid, Map, MapPinned, Search, User2, PlusCircle } from 'lucide-react';
import AppLogo from './app-logo';

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

interface AppSidebarProps {
    history?: HistoryItem[];
    onHistoryClick?: (item: HistoryItem) => void;
    onNewSearch?: () => void;
}

const mainNavItems: NavItem[] = [
     
      {
        title: 'Consultas',
        href: '/consulta',
        icon: Search,
    },
    //  {
    //     title: 'Matricula',
    //     href: '/consulta/maticula',
    //     icon:Barcode,

    // },
    //  {
    //     title: 'Solar & Manzana',
    //     href: '/consulta/SolarManzana',
    //     icon: Map,
    // },
    //  {
    //     title: 'Parcela & DC',
    //     href: '/consulta/ParcelaDC',
    //     icon: MapPinned,
    // },
 

    
];

const footerNavItems: NavItem[] = [
    {
        title: 'Mesa de ayuda',
        href: ' /',
        icon: Headphones
    },
    {
        title: 'SAP',
        href: '/ ',
        icon: BookOpen,
    },
];

export function AppSidebar({ history = [], onHistoryClick, onNewSearch }: AppSidebarProps) {
    return (
        <Sidebar collapsible="icon" variant="inset" className="h-full border-r">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>   
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
               
                {/* <div className="mt-4">
                   <p className="text-xs text-gray-500 px-2">Historial de Consultas</p>
                    <SidebarMenu>
                        {history.map((item, index) => (
                            <SidebarMenuItem key={index}>
                                <Link href="/consulta/history">
                                    <SidebarMenuButton size="sm" variant="default" className="w-full justify-start px-2 text-xs text-gray-400">
                                        {item.searchType === 'matricula'   }
                                        {item.searchType === 'solar_manzana'}  
                                        {item.searchType === 'parcela_dc' }
                                        <span className="truncate">{Object.values(item.params).filter(Boolean).join(', ')}</span>
                                    </SidebarMenuButton>
                                </Link>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </div> */}
            </SidebarContent>
 
            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
