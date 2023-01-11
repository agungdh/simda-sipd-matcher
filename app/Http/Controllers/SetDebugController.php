<?php

namespace App\Http\Controllers;

use DB;

class SetDebugController extends Controller
{
    public function check()
    {
        return DB::connection('sipd')
                    ->table('wp_options')
                    ->where('option_name', '_crb_singkron_simda_debug')
                    ->first();
    }

    public function yes()
    {
        DB::connection('sipd')
                    ->table('wp_options')
                    ->where('option_name', '_crb_singkron_simda_debug')
                    ->update(['option_value' => 1]);

        return $this->check();
    }

    public function no()
    {
        DB::connection('sipd')
                    ->table('wp_options')
                    ->where('option_name', '_crb_singkron_simda_debug')
                    ->update(['option_value' => 2]);

        return $this->check();
    }
}
