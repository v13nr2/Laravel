<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class MenuController extends Controller
{
    public function book() {
        $data = "Data All Menu";
        return response()->json($data, 200);
    }

    public function menualll() {
        //$data = "Welcome " . Auth::user()->name;
        $book = DB::table('nng_menu')->get();
        return response()->json($book, 200);
    }
}