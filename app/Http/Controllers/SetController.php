<?php

namespace App\Http\Controllers;

use DB;

class SetController extends Controller
{
    public function nonprogramkegiatan()
    {
        $nonProgram = DB::connection('simda')->table('ta_program')->where('kd_prog', 0)->first();
        $nonKegiatan = DB::connection('simda')->table('ta_kegiatan')->where('kd_keg', 0)->first();

        $subUnits = DB::connection('simda')->table('ref_sub_unit')->get();

        $subUnits->each(function ($subUnits) use ($nonProgram, $nonKegiatan) {
            $checkProgram = DB::connection('simda')->table('ta_program')->where([
                'tahun' => $nonProgram->tahun,
                'kd_urusan' => $subUnits->kd_urusan,
                'kd_bidang' => $subUnits->kd_bidang,
                'kd_unit' => $subUnits->kd_unit,
                'kd_sub' => $subUnits->kd_sub,
                'kd_prog' => $nonProgram->kd_prog,
                'id_prog' => $nonProgram->id_prog,
                'ket_program' => $nonProgram->ket_program,
                'kd_urusan1' => $nonProgram->kd_urusan1,
                'kd_bidang1' => $nonProgram->kd_bidang1,
            ])->first();

            if (! $checkProgram) {
                DB::connection('simda')->table('ta_program')->insert([
                    'tahun' => $nonProgram->tahun,
                    'kd_urusan' => $subUnits->kd_urusan,
                    'kd_bidang' => $subUnits->kd_bidang,
                    'kd_unit' => $subUnits->kd_unit,
                    'kd_sub' => $subUnits->kd_sub,
                    'kd_prog' => $nonProgram->kd_prog,
                    'id_prog' => $nonProgram->id_prog,
                    'ket_program' => $nonProgram->ket_program,
                    'kd_urusan1' => $nonProgram->kd_urusan1,
                    'kd_bidang1' => $nonProgram->kd_bidang1,
                ]);
            }

            $checkKegiatan = DB::connection('simda')->table('ta_kegiatan')->where([
                'tahun' => $nonKegiatan->tahun,
                'kd_urusan' => $subUnits->kd_urusan,
                'kd_bidang' => $subUnits->kd_bidang,
                'kd_unit' => $subUnits->kd_unit,
                'kd_sub' => $subUnits->kd_sub,
                'kd_prog' => $nonKegiatan->kd_prog,
                'id_prog' => $nonKegiatan->id_prog,
                'kd_keg' => $nonKegiatan->kd_keg,
                'ket_kegiatan' => $nonKegiatan->ket_kegiatan,
                'status_kegiatan' => $nonKegiatan->status_kegiatan,
            ])->first();

            if (! $checkKegiatan) {
                DB::connection('simda')->table('ta_kegiatan')->insert([
                    'tahun' => $nonKegiatan->tahun,
                    'kd_urusan' => $subUnits->kd_urusan,
                    'kd_bidang' => $subUnits->kd_bidang,
                    'kd_unit' => $subUnits->kd_unit,
                    'kd_sub' => $subUnits->kd_sub,
                    'kd_prog' => $nonKegiatan->kd_prog,
                    'id_prog' => $nonKegiatan->id_prog,
                    'kd_keg' => $nonKegiatan->kd_keg,
                    'ket_kegiatan' => $nonKegiatan->ket_kegiatan,
                    'status_kegiatan' => $nonKegiatan->status_kegiatan,
                ]);
            }
        });
    }
}
