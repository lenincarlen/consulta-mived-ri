import { useState, useMemo, useRef, useEffect } from 'react';
import api from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/app-layout';
import { FileText, Map, MapPin, Search, User, X, Barcode, MapPinned, Printer, Download, History } from 'lucide-react';
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/react';

type SearchType = 'matricula' | 'solar_manzana' | 'parcela_dc';

interface HistoryItem {
  searchType: SearchType;
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

const ConsultaPage = ({ user }: { user: { name: string } }) => {
  const [searchType, setSearchType] = useState<SearchType>('matricula');
  const [matricula, setMatricula] = useState('');
  const [solar, setSolar] = useState('');
  const [manzana, setManzana] = useState('');
  const [parcela, setParcela] = useState('');
  const [dc, setDc] = useState('');
  const [results, setResults] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [history, setHistory] = useState<HistoryItem[]>([]);
  const [lastSearched, setLastSearched] = useState<HistoryItem | null>(null);
  const resultsRef = useRef(null);

  useEffect(() => {
    const storedHistory = localStorage.getItem('consultaHistory');
    if (storedHistory) {
      setHistory(JSON.parse(storedHistory));
    }
  }, []);

  const handleSearch = async () => {
    setLoading(true);
    setError(null);
    setResults([]);

    const searchParams = { matricula, solar, manzana, parcela, dc };
    const newHistoryItem: HistoryItem = { searchType, params: searchParams, timestamp: new Date().toISOString(), user };

    try {
      let response;
      if (searchType === 'matricula') {
        response = await api.get(`/inmueble/getpropertiesbymatricula?matricula=${matricula}`);
      } else if (searchType === 'solar_manzana') {
        response = await api.get(`/inmueble/getPropertiesBySolarAndManzana?solar=${solar}&manzana=${manzana}`);
      } else {
        response = await api.get(`/inmueble/getPropertiesByParcelaAndDC?parcela=${parcela}&dc=${dc}`);
      }
      setResults(response.data.data);
      
      const updatedHistory = [newHistoryItem, ...history];
      setHistory(updatedHistory);
      localStorage.setItem('consultaHistory', JSON.stringify(updatedHistory));
      setLastSearched(newHistoryItem);
    } catch (err) {
      setError('Error al realizar la consulta');
    } finally {
      setLoading(false);
    }
  };

  const handleHistoryClick = (item: HistoryItem) => {
    setSearchType(item.searchType);
    setMatricula(item.params.matricula || '');
    setSolar(item.params.solar || '');
    setManzana(item.params.manzana || '');
    setParcela(item.params.parcela || '');
    setDc(item.params.dc || '');
    handleSearch();
  };

  const breadcrumbs: BreadcrumbItem[] = [
      {
          title: 'Consulta de Inmuebles ',
          href: '/consulta/page',
      },
  ];

  const handleClear = () => {
    setMatricula('');
    setSolar('');
    setManzana('');
    setParcela('');
    setDc('');
    setResults([]);
    setError(null);
    setLastSearched(null);
  };

  const isSearchDisabled = useMemo(() => {
    if (searchType === 'matricula') {
      return !matricula;
    }
    if (searchType === 'solar_manzana') {
      return !solar || !manzana;
    }
    if (searchType === 'parcela_dc') {
      return !parcela || !dc;
    }
    return true;
  }, [searchType, matricula, solar, manzana, parcela, dc]);

  const handlePrint = () => {
    window.print();
  };

  const handleExport = () => {
    const headers = [
      "Matrícula", "Fecha Inscripción", "Superficie", "DC Posicional", "Parcela", "Solar", "Manzana",
      "Porción", "Distrito Catastral", "Código Unidad Funcional", "Nombre Condominio", "Municipio",
      "Provincia", "Oficina Registral"
    ];
    const csvContent = [
      headers.join(','),
      ...results.map(item => headers.map(header => item[header.toLowerCase().replace(/ /g, '')] || 'N/A').join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    if (link.href) {
      URL.revokeObjectURL(link.href);
    }
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', 'resultados_inmuebles.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const renderResult = (item: any, index: number) => (
    <Card key={index} className=" w-full  -mt-9 shadow-none border-0 ">
      <CardHeader className=' border-b-1 shadow-4xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex flex-row justify-between items-center'>
        <div className="flex items-center">
        
          <div>
            <CardTitle>Resultado de la Búsqueda</CardTitle>
            {lastSearched && (
              <p className="text-xs text-gray-500 mb-2 mt-1.5 flex">
                Última consulta: <p className="text-green-600 font-bold ml-1.5"> {new Date(lastSearched.timestamp).toLocaleString()}</p>
              
              </p>
            )}
          </div>
        </div>
        <div className="flex space-x-2">
            <Button onClick={handleClear} variant="link" className="rounded-l-none" disabled={loading}>
                    <X className="w-5 h-5" />
                    Limpiar consulta
                  </Button>
          <Button variant="outline" size="icon" onClick={handlePrint}>
            <Printer className="h-4 w-4" />
          </Button>
          <Button variant="outline" size="icon" onClick={handleExport}>
            <Download className="h-4 w-4" />
          </Button>
        </div>
      </CardHeader>
<CardContent className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-0 px-4 bg-white">
  {[
    { label: "Matrícula", value: item.matricula, valueClass: "text-red-700" },
    { label: "Fecha Inscripción", value: item.fechaInscripcion },
    { label: "Superficie", value: item.superficie },
    { label: "DC Posicional", value: item.dcPosicional },
    { label: "Parcela", value: item.parcela },
    { label: "Solar", value: item.solar },
    { label: "Manzana", value: item.manzana },
    { label: "Porción", value: item.porcion },
    { label: "Distrito Catastral", value: item.distritoCatastral },
    { label: "Unidad Funcional", value: item.codigoUnidadFuncional },
    { label: "Nombre Condominio", value: item.nombreCondominio },
    { label: "Municipio", value: item.municipio },
    { label: "Provincia", value: item.provincia },
    { label: "Oficina de Registro", value: item.oficinaRegistral },
  ].map((field, idx) => (
    <div
      key={idx}
      className="flex flex-col    p-2 border-b "
    >
      <p className="text-sm font-semibold text-gray-800 tracking-wide uppercase">
        {field.label}:
      </p>
      <span
        className={`mt-1 text-sm font-medium text-gray-700 ${field.valueClass || ""}`}
      >
        {field.value || "N/A"}
      </span>
    </div>
  ))}
</CardContent>

    </Card>
  );

  const SkeletonCard = () => (
    <Card className="mt-4 w-full border-0 shadow-none">
      <CardHeader>
        <Skeleton className="h-6 w-1/2" />
      </CardHeader>
      <CardContent className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {Array.from({ length: 14 }).map((_, i) => (
          <div key={i} className="space-y-3">
            <Skeleton className="h-4 w-1/4" />
            <Skeleton className="h-4 w-3/4" />
          </div>
        ))}
      </CardContent>
    </Card>
  );

  return (
    <AppLayout breadcrumbs={breadcrumbs} history={history} onHistoryClick={handleHistoryClick} onNewSearch={handleClear}>
            <Head title="Consulta de Inmuebles" />
            
      <div className="container mx-auto ">
        <Card className="border-0 shadow-none">
          <CardHeader>
             
            <p className="text-sm text-gray-600">Seleccione el tipo de búsqueda y complete los campos requeridos</p>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-0 w-3/4">
              <Button
                variant={searchType === 'matricula' ? 'link' : 'outline'}
                className="w-full justify-start rounded-none "
                onClick={() => setSearchType('matricula')}
              >
                <Barcode className="w-5 h-5   text-gray" />
                Por Matrícula
              </Button>
              <Button
                variant={searchType === 'solar_manzana' ? 'link' : 'outline'}
                className="w-full justify-start rounded-none"
                onClick={() => setSearchType('solar_manzana')}
              >
                <Map className="w-5 h-5 mr-2 text-gray" />
                Por Solar y Manzana
              </Button>
              <Button
                variant={searchType === 'parcela_dc' ? 'link' : 'outline'}
                className="w-full justify-start rounded-none"
                onClick={() => setSearchType('parcela_dc')}
              >
                <MapPinned className="w-5 h-5 mr-2 text-gray" />
                Por Parcela y DC
              </Button>
            </div>

            <div className="mt-6  w-2/4">
              {searchType === 'matricula' && (
                <div className="flex">
                  <Input 
                    placeholder="Ingrese la matrícula "
                    value={matricula} 
                    onChange={(e) => setMatricula(e.target.value)} 
                    className="rounded-r-none"
                  />
                  <Button onClick={handleSearch} className="rounded-l-none" disabled={loading || isSearchDisabled}>
                    <Search className="w-5 h-5" />
                  </Button>
               
                </div>
              )}
              {searchType === 'solar_manzana' && (
                <div className="flex">
                  <Input placeholder="Ingrese el solar" value={solar} onChange={(e) => setSolar(e.target.value)} className="rounded-r-none" />
                  <Input placeholder="Ingrese la manzana" value={manzana} onChange={(e) => setManzana(e.target.value)} className="rounded-none" />
                  <Button onClick={handleSearch} className="rounded-l-none" disabled={loading || isSearchDisabled}>
                    <Search className="w-5 h-5" />
                  </Button>
                 
                </div>
              )}
              {searchType === 'parcela_dc' && (
                <div className="flex">
                  <Input placeholder="Ingrese la parcela" value={parcela} onChange={(e) => setParcela(e.target.value)} className="rounded-r-none" />
                  <Input placeholder="Ingrese el DC" value={dc} onChange={(e) => setDc(e.target.value)} className="rounded-none" />
                  <Button onClick={handleSearch} className="rounded-l-none" disabled={loading || isSearchDisabled}>
                    <Search className="w-5 h-5" />
                  </Button>
                
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        <div className="mt-3 grid grid-cols-1 " ref={resultsRef}>
          {loading && <SkeletonCard />}
          {error && <p className="text-red-500">{error}</p>}
          {!loading && results.length > 0 && (
            <div className="space-y-4">
              {results.map(renderResult)}
            </div>
          )}
        </div>
      </div>
      <Head title="Consulta de Inmuebles" />
    </AppLayout>
  );
};

export default ConsultaPage;
