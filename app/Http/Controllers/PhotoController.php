<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserPhoto;
use Mail;
use App\Mail\MailNotification;
use Validator;
use DB;
use Storage;

class PhotoController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->res['message'] = '';
        $this->status_code = 204;
        date_default_timezone_set('America/Mexico_City');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $photo_list = [];
        $user_id = $this->request->user()->id;
        $rol_id = $this->request->user()->rol_id;

        if ($rol_id != null) {
            if ($rol_id == 1) {
                if ($user_id != null) {
                    $photo_list = UserPhoto::where('user_id', $user_id)->get();
                    $this->res['data'] = $photo_list;
                    $this->status_code = 200;
                } else {
                    $this->res['message'] = 'No hay fotos cargadas hasta el momento.';
                    $this->status_code = 422;
                }
            } else if ($rol_id == 2){
                $photo_list = UserPhoto::all();
                $this->res['data'] = $photo_list;
                $this->status_code = 200;
            }
        }

        return response($this->res, $this->status_code);
    }

    public function search() {
        $validator = Validator::make($this->request->all(), [
            'lat' => 'required',
            'lon' => 'required'
        ]);
        if ($validator->fails()){
            return response(['errors'=>$validator->errors()->all()], 422);
        }

        $distance = 5;
        $earthRadius = 6371;
        $lat = $this->request->input('lat');
        $lon = $this->request->input('lon');
        $box = $this->getBoundaries($lat, $lon, $distance, $earthRadius);

        $q = UserPhoto::with('user');

        $distance_select = sprintf(
            "           
            ROUND(( %d * acos( cos( radians(%s) ) " .
                    " * cos( radians( lat ) ) " .
                    " * cos( radians( lon ) - radians(%s) ) " .
                    " + sin( radians(%s) ) * sin( radians( lat ) ) " .
                " ) " . 
            ")
            , 2 ) " . 
            "AS distance
            ",
            $earthRadius,               
            $lat,
            $lon,
            $lat
           );

        $q = $q->select(DB::raw('user_id, '.$distance_select))
                    ->having( 'distance', '<=', $distance )
                    ->get();
        $this->res['data'] = $q;
        $this->status_code = 200;

        return response($this->res, $this->status_code);
    }

    public function getBoundaries($lat, $lng, $distance, $earthRadius) {
        $return = array();
     
        // Los angulos para cada dirección
        $cardinalCoords = array('north' => '0',
                                'south' => '180',
                                'east' => '90',
                                'west' => '270');

        $rLat = deg2rad($lat);
        $rLng = deg2rad($lng);
        $rAngDist = $distance/$earthRadius;

        foreach ($cardinalCoords as $name => $angle)
        {
            $rAngle = deg2rad($angle);
            $rLatB = asin(sin($rLat) * cos($rAngDist) + cos($rLat) * sin($rAngDist) * cos($rAngle));
            $rLonB = $rLng + atan2(sin($rAngle) * sin($rAngDist) * cos($rLat), cos($rAngDist) - sin($rLat) * sin($rLatB));

            $return[$name] = array('lat' => (float) rad2deg($rLatB), 
                                    'lng' => (float) rad2deg($rLonB));
        }

        return array('min_lat'  => $return['south']['lat'],
                    'max_lat' => $return['north']['lat'],
                    'min_lng' => $return['west']['lng'],
                    'max_lng' => $return['east']['lng']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try {
            $validator = Validator::make($this->request->all(), [
                'file' => 'required'
            ]);
            if ($validator->fails()){
                return response(['errors'=>$validator->errors()->all()], 422);
            }

            $lat = $this->request->input('lat');
            $lon = $this->request->input('lon');
            $user_id = $this->request->user()->id;
            $rol_id = $this->request->user()->rol_id;

            //SI EL USUARIO LOGUEADO TIENE ROL DE USUARIO
            if ($rol_id == 1) {
                $uploadedFile = $this->request->file('file');

                $path = Storage::putFile('photos', $uploadedFile);
                
                $photo = new UserPhoto();
                $photo->url = $path;
                $photo->user_id = $user_id;
                $photo->lat = $lat;
                $photo->lon = $lon;
                $photo->save();

                $this->res['message'] = 'Foto cargada correctamente.';
                $this->res['data'] = $photo;
                $this->status_code = 200;
            } else {
                //SI EL USUARIO LOGUEADO TIENE ROL DE MODERADOR
                $this->res['message'] = 'No tiene permisos para agregar fotos.';
                $this->status_code = 422;

            }
        } catch (Exception $e) {
            return response(['error' => 'Error inesperado, contacte al administrador.'], 500);
        }
        return response($this->res, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $user_id = $this->request->user()->id;
        $rol_id = $this->request->user()->rol_id;

        $photo = UserPhoto::with('user')->where('id', $id)->first();

        if ($rol_id == 1) {
            if ($user_id != null) {
                if ($photo) {
                    $photo->delete();
                    $this->res['message'] = 'Foto eliminada correctamente.';
                    $this->status_code = 200;
                } else {
                    $this->res['message'] = 'Error al intentar eliminar la foto.';
                    $this->status_code = 422;
                }
            }
        } else if ($rol_id == 2) {
            if ($photo) {
                $data = new \stdClass();
                $data->name = $photo->user->name;

                Mail::to($photo->user->email)->send(new MailNotification($data));
                $photo->delete();
                $this->res['message'] = 'Foto eliminada correctamente. email: '.$photo->user->email;
                $this->status_code = 200;
            } else {
                $this->res['message'] = 'Error al intentar eliminar la foto.';
                $this->status_code = 422;
            }
        }
        return response($this->res, $this->status_code);
    }
}
