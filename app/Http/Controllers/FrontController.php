<?php

namespace BovinApp\Http\Controllers;

use Illuminate\Http\Request;

use BovinApp\Http\Requests;

use BovinApp\Farm;
use BovinApp\Badamecum;
use BovinApp\SaleAnimal;

class FrontController extends Controller
{
    public function index()
    { 
    	return view('main.index');

    }
    public function cotacto(){

    	return view('cotacto');
    }

    public function admin()
    {

    	return view('admin.index');
    }
    public function search(Request $request)
    {
        
        $farms=Farm::where('name', 'ILIKE', '%' . trim($request -> get(trim('name'))) . '%') -> get();

        return view('main.search',compact('farms'));   
    }

     public function info_farm(Request $request)
    {
        
        $farms=Farm::where('id', $request->id)->get();
        //dd($farms);

        return view('main.info_farm',compact('farms'));   
    } 

    public function show_bademecum()
    {        
        $badamecums = Badamecum::orderBy('id', 'desc')->paginate(6);
        return view('main.show_badamecum', compact('badamecums'));
    }
     public function detail_badamecum($slug)
    {
        
        $badamecum= Badamecum::where('slug',$slug)->first();
        return view('main.detail-badamecum',compact('badamecum'));

    }

     public function sale_animal()
    {
       $sales = SaleAnimal::orderBy('id','desc')->paginate(6);//dd($sales);
       return view('main.sale_animal',compact('sales'));
    }
      public function info_sale($slug)
    {
       $sale = SaleAnimal::where('slug',$slug)->first();//dd($sale);
       return view('main.info_sale',compact('sale'));
    }

}
 
 