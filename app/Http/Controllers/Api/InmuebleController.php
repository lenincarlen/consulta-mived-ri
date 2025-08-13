<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InmuebleController extends Controller
{
    public function getPropertiesByMatricula(Request $request)
    {
        $matricula = $request->query('matricula');
        $response = Http::withHeaders([
            'Api-Key' => env('VITE_API_KEY'),
        ])->get(env('VITE_API_URL') . '/inmueble/getpropertiesbymatricula', [
            'matricula' => $matricula,
        ]);

        return $response->json();
    }

    public function getPropertiesBySolarAndManzana(Request $request)
    {
        $solar = $request->query('solar');
        $manzana = $request->query('manzana');
        $response = Http::withHeaders([
            'Api-Key' => env('VITE_API_KEY'),
        ])->get(env('VITE_API_URL') . '/inmueble/getPropertiesBySolarAndManzana', [
            'solar' => $solar,
            'manzana' => $manzana,
        ]);

        return $response->json();
    }

    public function getPropertiesByParcelaAndDC(Request $request)
    {
        $parcela = $request->query('parcela');
        $dc = $request->query('dc');
        $response = Http::withHeaders([
            'Api-Key' => env('VITE_API_KEY'),
        ])->get(env('VITE_API_URL') . '/inmueble/getPropertiesByParcelaAndDC', [
            'parcela' => $parcela,
            'dc' => $dc,
        ]);

        return $response->json();
    }
}
