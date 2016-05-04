<?php

namespace BovinApp\Http\Controllers;

use Illuminate\Http\Request;

use BovinApp\Http\Requests;
use BovinApp\Farm;

use Auth;
use Input;



class FarmCOntroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()    
    {
        $farms = Farm::where('idUser',Auth::id())
                -> paginate(3);
        return view('farm.index', compact('farms'));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("farm.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules =array(
            'name'                  => 'required',  
            'agent'                 => 'required',
            'operationCertificate'  => 'required',          
            'address'               => 'required'
            );

        $this->validate($request,$rules);    

        $farm = new \BovinApp\Farm($request->all());

        $id_users= Auth::id(); 
        $farm->idUser = $id_users;
        $message = $farm ? 'Finca agregada correctamente!' : 'La finca NO pudo agregarse!'; 

        if($request->patent=""){     dd($request->patent); 

            $farm->patent = 'farm.jpg'; 
            
            $farm->save();
            return redirect() -> route('farm.index')->with('message', $message); 
        }
            $file = Input::file('patent');//Creamos una instancia de la libreria instalada

            $patent = \Image::make(\Input::file('patent'));//Ruta donde queremos guardar las imagenes
            $path = 'img/farm/';          
                // Cambiar de tamaño
            $patent -> resize(358, 141);
            $patent -> save($path . $file -> getClientOriginalName());  
            $farm->patent = $file -> getClientOriginalName();;
            $farm->save();


            return redirect() -> route('farm.index')->with('message', $message);
    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Farm $farm)
    {
        return $farm;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
