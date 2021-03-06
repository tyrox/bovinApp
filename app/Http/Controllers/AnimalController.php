<?php

namespace BovinApp\Http\Controllers;
use Illuminate\Http\Request;
use BovinApp\Http\Requests;


use BovinApp\Animal;
use BovinApp\Natural_mating;
use BovinApp\Invitro_fertilization;
use BovinApp\Embryo_transfer;
use BovinApp\Artificial_insemination;
use BovinApp\Farm;
use Auth;
use Session;
use Input;



class AnimalController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        
     

         if(Session::get('idfarm'))
          {

            $animals= Animal::where('idFarm',Session::get('idfarm'))
                              ->where('idUser',Auth::id())  
                            -> paginate(8);
             $farm= Session::get('farm'); //dd($animals);
            return view('animal.index',compact('animals','farm')); 
          
            
        }else
        {
            $message = 'Debes Seleccionar una Finca';
            return redirect() -> route('farm-index')->with('message', $message);

        }
          
       
    }

    public function search(Request $request)
    {
        $farm= Session::get('farm');

     if($request->number=="on"){
        $animals=\DB::table('animals')->where('idFarm',Session::get('idfarm')) 
                                        ->where('idUser',Auth::id())                                      
                                       ->where('animalNumber', 'ILIKE', '%' . trim($request -> get(trim('name'))) . '%')
                                       
                                        -> paginate(8);

      }else{
        $animals=\DB::table('animals')->where('idFarm',Session::get('idfarm'))
                                      ->where('idUser',Auth::id()) 
                                       ->where('name', 'ILIKE', '%' . trim($request -> get(trim('name'))) . '%')                                     
                                       
                                        -> paginate(8);//dd($animals);    


      }

      if(count($animals)){

        return view('animal.index',compact('animals','farm'));   
      }else{
       $message = 'No hay concidencias';
            return redirect() -> route('animal-index')->with('message', $message);
      }

        

        
         

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view("animal.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            //Validaciones
        $rules =array(              
                        'name'               => 'required',                       
                        'breed'              => 'required',                      
                        'gender'             => 'required',
                        'date'               => 'required',
                        'feature'            => 'required'  
                      );
        $this->validate($request,$rules);

   

        
          if(Session::get('idfarm'))
          {

                $animal = new Animal();
                $animal->idUser = Auth::id();
                $animal->idFarm=Session::get('idfarm');                
                $animal->name=$request->name;
                $animal->animalNumber=$this->get_animal_number();
                $animal->slug=str_slug($request->get('name'));
                $animal->breed=$request->breed;
                $animal->gender=$request->gender;
                $animal->feature=$request->feature;
                $animal->birthdate=$request->date; 
                $animal->status=0;
                $animal->status_deathDate=0;


                    //Validacion de imagen 
                if (Input::hasFile('image')) 
                {   
                    $file = Input::file('image');//Creamos una instancia de la libreria instalada
                    $image = \Image::make(\Input::file('image'));//Ruta donde queremos guardar las imagenes

                    $path = 'img/animal/';

                    // Cambiar de tamaño
                    $image -> resize(331, 152);
                    $image -> save($path . $file -> getClientOriginalName());   
                    $animal->image = $file -> getClientOriginalName();
                    //$animal->save(); 
                    //return redirect() -> route('animal.index');
                 }else
                 {
                    //Si no hay imagen, se guarda una por defecto
                    $image='animal';  
                    $default = 'animal.jpg';
                    $animal->image = $default;              
                    //$animal->save();
                 }

                 $animal->save();


                 if($request->fi=="fi"){

                    $invitro_fertilizations= new Invitro_fertilization();
                    $invitro_fertilizations->father=$request->father;
                    $invitro_fertilizations->donorMother=$request->donorMother;
                    $invitro_fertilizations->receivingMother=$request->receivingMother;
                    $animal->origin()->save($invitro_fertilizations);
                 }

                 if($request->ia=='ia'){

                    $artificial_inseminations= new Artificial_insemination();
                    $artificial_inseminations->father=$request->father;
                    $artificial_inseminations->mother=$request->mother;
                    $animal->origin()->save($artificial_inseminations);
                 }
                 if($request->mt=='mt'){

                    $natural_matings= new Natural_mating();
                    $natural_matings->father=$request->father;
                    $natural_matings->mother=$request->mother;
                    $animal->origin()->save($natural_matings);
                 }
                 if($request->te=='te'){

                    $embryo_transfers= new Embryo_transfer();
                    $embryo_transfers->father=$request->father;
                    $embryo_transfers->donorMother=$request->donorMother;
                    $embryo_transfers->receivingMother=$request->receivingMother;
                    $animal->origin()->save($embryo_transfers);
                 }
            return redirect() -> route('animal-index');
          
            
        }else
        {
            $message = 'Debes Seleccionar una Finca';
            return redirect() -> route('farm-index')->with('message', $message);

        }



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $animal=Animal::where('slug',$slug)->first();
        Session::put('animal',$slug); 
        Session::put('idAnimal',$animal->id);//get idAnimal for store new production register 
       
        return view('animal.show',compact('animal'));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug, Request $request)
    {
        //$animal=Animal::findOrFail(Session::get('idAnimal'));dd($animal);

        $animal=Animal::where('idUser',Auth::id())
                        ->where('slug',$slug)
                        ->first();
       
        return view('animal.edit', compact('animal'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Animal $animal)
    {
        $animal = Animal::findOrFail($animal->id); 
                              //Validaciones
        $rules =array(              
                        'name'               => 'required',                       
                        'breed'              => 'required',                      
                        'gender'             => 'required',                        
                        'feature'            => 'required',                 
                      );
        $this->validate($request,$rules);

        
       // dd($request->breed);

        $animal->registrationNumber=$request->registrationNumber;       
        
        $animal->name=$request->name;   
        $animal->slug=str_slug($request->get('name'));
        $animal->breed=$request->breed;       
        $animal->gender=$request->gender;
        $animal->feature=$request->feature;
        $animal->birthdate=$request->birthdate;
        $animal->deathDate=$request->deathdate;
                    //Validacion de imagen 
        if (Input::hasFile('image')) 
        {   
            $file = Input::file('image');//Creamos una instancia de la libreria instalada
            $image = \Image::make(\Input::file('image'));//Ruta donde queremos guardar las imagenes

            $path = 'img/animal/';

            // Cambiar de tamaño
            $image -> resize(331, 152);
            $image -> save($path . $file -> getClientOriginalName());   
            $animal->image = $file -> getClientOriginalName();
           
         }else
         {
            //Si no hay imagen, se guarda la misma imagen original antes de actualizar           
            $animal->image = $animal->image;         
         }  

        if($request->has('deathdate'))
        {
            $animal->status_deathDate= 1;
        }    
                     
        $updated = $animal->save();
        
        $message = $updated ? 'Animal actualizado correctamente!' : ' La animal NO pudo actualizarse!';
        
        return redirect()->route('animal-index')->with('message', $message);
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

     public function get_animal_number()
    {
        $farms=Farm::where('id',Session::get('idfarm'))->get();
        $count= Animal::where('idFarm',Session::get('idfarm'))
                          -> count();        
          $count++;


        foreach ($farms as $farm) {

            $operationCertificate=$farm->operationCertificate;
            
        }

        $animal_number=$count.'-'.$operationCertificate;

        return $animal_number;
    }
}
