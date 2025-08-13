import { useState, useEffect, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { ChevronLeft, ChevronRight } from 'lucide-react';

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

const HistoryPage = ({ user }: { user: { name: string } }) => {
  const [history, setHistory] = useState<HistoryItem[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 7;

  useEffect(() => {
    const storedHistory = localStorage.getItem('consultaHistory');
    if (storedHistory) {
      setHistory(JSON.parse(storedHistory));
    }
  }, []);

  const paginatedHistory = useMemo(() => {
    const startIndex = (currentPage - 1) * itemsPerPage;
    return history.slice(startIndex, startIndex + itemsPerPage);
  }, [history, currentPage]);

  const totalPages = Math.ceil(history.length / itemsPerPage);

  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Historial de Consultas',
      href: '/consulta/history',
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Historial de Consultas" />
      <div className="container mx-auto w-5xl p-4">
        <Card className="border-0 shadow-none">
          <CardHeader>
            <CardTitle>Historial de Consultas</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader className="">
                <TableRow >
                  <TableHead>Tipo de Búsqueda</TableHead>
                  <TableHead>Parámetros</TableHead>
                  <TableHead>Fecha y Hora</TableHead>
                  <TableHead>Usuario</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {paginatedHistory.map((item, index) => (
                  <TableRow key={index}>
                    <TableCell>{item.searchType}</TableCell>
                    <TableCell>{JSON.stringify(item.params)}</TableCell>
                    <TableCell>{new Date(item.timestamp).toLocaleString()}</TableCell>
                    <TableCell>{item.user?.name || 'N/A'}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
            <div className="flex justify-end items-center mt-4">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                disabled={currentPage === 1}
              >
                <ChevronLeft className="h-4 w-4" />
                Anterior
              </Button>
              <span className="mx-4 text-sm">
                Página {currentPage} de {totalPages}
              </span>
              <Button
                variant="outline"
                size="sm"
                onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                disabled={currentPage === totalPages}
              >
                Siguiente
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
};

export default HistoryPage;
