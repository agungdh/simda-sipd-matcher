<?php

namespace App\Http\Controllers;

use DB;

class ResetController extends Controller
{
    public function rka()
    {
        DB::connection('simda')->table('ta_belanja_rinc_sub')->delete();
        DB::connection('simda')->table('ta_belanja_rinc')->delete();
        DB::connection('simda')->table('ta_belanja')->delete();
        DB::connection('simda')->table('ta_pembiayaan')->delete();
        DB::connection('simda')->table('ta_kegiatan')->delete();
        DB::connection('simda')->table('ta_program')->delete();
    }
}
