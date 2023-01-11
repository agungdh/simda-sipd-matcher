<?php

namespace App\Http\Controllers;

use DB;

class CheckController extends Controller
{
    private function simdaTransformer($simda)
    {
        $simda->each(function ($simdaData) {
            $simdaData->kode_skpd = implode('.', [
                $simdaData->kd_urusan,
                $simdaData->kd_bidang,
                $simdaData->kd_unit,
                $simdaData->kd_sub,
            ]);
        });
    }

    private function getSimdaUnit()
    {
        $simda = DB::connection('simda');

        $simda = $simda->table('ref_sub_unit as rsu');
        $simda = $simda->select([
            'rsu.kd_urusan',
            'rsu.kd_bidang',
            'rsu.kd_unit',
            'rsu.kd_sub',
            'rsu.nm_sub_unit as nama_skpd',
        ]);
        $simda = $simda->where('rsu.kd_sub', '!=', 0);

        $simda = $simda->get();

        return $simda;
    }

    private function showSimdaUnit($kode_skpd)
    {
        $simda = DB::connection('simda');

        $simda = $simda->table('ref_sub_unit as rsu');
        $simda = $simda->select([
            'rsu.kd_urusan',
            'rsu.kd_bidang',
            'rsu.kd_unit',
            'rsu.kd_sub',
            'rsu.nm_sub_unit as nama_skpd',
        ]);
        $simda = $simda->where('rsu.kd_sub', '!=', 0);

        $kode_skpd_array = explode('.', $kode_skpd);
        $simda = $simda->where([
            'kd_urusan' => $kode_skpd_array[0],
            'kd_bidang' => $kode_skpd_array[1],
            'kd_unit' => $kode_skpd_array[2],
            'kd_sub' => $kode_skpd_array[3],
        ]);

        $simda = $simda->first();

        if ($simda) {
            $simda->kode_skpd = implode('.', [
                $simda->kd_urusan,
                $simda->kd_bidang,
                $simda->kd_unit,
                $simda->kd_sub,
            ]);
        }

        return $simda;
    }

    public function rka()
    {
        $stat['start'] = date('Y-m-d H:i:s');

        $stat['sipd']['total'] = 0;
        $stat['simda']['total'] = 0;
        $stat['stat']['total'] = 0;

        $stat['sipd']['jumlah'] = 0;
        $stat['simda']['jumlah'] = 0;
        $stat['stat']['jumlah'] = 0;

        $finished = collect();
        $unfinished = collect();

        $subUnits = DB::connection('simda')->table('ref_sub_unit')->get();

        $subUnits->each(function ($subUnit) use (&$stat, $finished, $unfinished) {
            $simda = DB::connection('simda')->table('ta_belanja_rinc_sub')->where([
                'kd_urusan' => $subUnit->kd_urusan,
                'kd_bidang' => $subUnit->kd_bidang,
                'kd_unit' => $subUnit->kd_unit,
                'kd_sub' => $subUnit->kd_sub,
            ]);

            $sipd = DB::connection('sipd');

            $sipd = $sipd->table('data_rka as dr');
            $sipd = $sipd->join('data_sub_keg_bl as dskb', function ($join) {
                $join->on('dr.kode_bl', '=', 'dskb.kode_bl');
                $join->on('dr.kode_sbl', '=', 'dskb.kode_sbl');
            });
            $sipd = $sipd->join('data_unit as du', 'dskb.id_sub_skpd', '=', 'du.id_skpd');
            $sipd = $sipd->join('wp_options as wo', 'du.id_skpd', '=', DB::raw('substring(wo.option_name, 11, 4)'));
            $sipd = $sipd->where('option_value', implode('.', [
                $subUnit->kd_urusan,
                $subUnit->kd_bidang,
                $subUnit->kd_unit,
                $subUnit->kd_sub,
            ]));

            $subUnit->sipd['total'] = $sipd->sum('rincian');
            $subUnit->simda['total'] = $simda->sum('total');

            $subUnit->sipd['jumlah'] = $sipd->count('rincian');
            $subUnit->simda['jumlah'] = $simda->count('total');

            $subUnit->stat['total'] = $subUnit->simda['total'] - $subUnit->sipd['total'];
            $subUnit->stat['jumlah'] = $subUnit->simda['jumlah'] - $subUnit->sipd['jumlah'];

            $stat['simda']['total'] += $subUnit->simda['total'];
            $stat['simda']['jumlah'] += $subUnit->simda['jumlah'];
            $stat['sipd']['total'] += $subUnit->sipd['total'];
            $stat['sipd']['jumlah'] += $subUnit->sipd['jumlah'];
            $stat['stat']['total'] += $subUnit->stat['total'];
            $stat['stat']['jumlah'] += $subUnit->stat['jumlah'];

            if ($subUnit->stat['total'] == 0 && $subUnit->stat['jumlah'] == 0) {
                $finished->push($subUnit);
            } else {
                $unfinished->push($subUnit);
            }
        });

        $stat['end'] = date('Y-m-d H:i:s');
        $stat['duration'] = strtotime($stat['end']) - strtotime($stat['start']);

        return compact([
            'stat',
            'finished',
            'unfinished',
            'subUnits',
        ]);
    }

    private function getSipdUnit()
    {
        $sipd = DB::connection('sipd');

        $sipd = $sipd->table('wp_options as wo');
        $sipd = $sipd->select([
            'wo.option_value as kode_skpd',
            'du.nama_skpd',
        ]);
        $sipd = $sipd->whereRaw('wo.option_name like ?', ['_crb_unit_%']);
        $sipd = $sipd->join('data_unit as du', 'du.id_skpd', '=', DB::raw('substring(wo.option_name, 11, 4)'));

        $sipd = $sipd->get();

        return $sipd;
    }

    public function unitError()
    {
        $duplicates = $this->duplicateUnit();
        $malformeds = $this->malformedUnit();

        return compact([
            'duplicates',
            'malformeds',
        ]);
    }

    private function duplicateUnit()
    {
        $sipd = DB::connection('sipd');

        $sipd = $sipd->table('wp_options');
        $sipd = $sipd->select(
            'option_value',
            DB::raw('COUNT(option_value) as total'),
        );
        $sipd = $sipd->whereRaw('option_name like ?', ['_crb_unit_%']);
        $sipd = $sipd->groupBy('option_value');
        $sipd = $sipd->having(DB::raw('COUNT(option_value)'), '>', 1);

        $sipd = $sipd->get();

        return $sipd;
    }

    private function malformedUnit()
    {
        $sipd = DB::connection('sipd');

        $sipd = $sipd->table('wp_options');
        $sipd = $sipd->select(
            'option_value',
            DB::raw('
                ROUND (   
                    (
                        LENGTH(option_value)
                        - LENGTH( REPLACE ( option_value, ".", "") ) 
                    ) / LENGTH(".")        
                ) AS total
            '),
        );
        $sipd = $sipd->whereRaw('option_name like ?', ['_crb_unit_%']);
        $sipd = $sipd->whereRaw('
            ROUND (   
                (
                    LENGTH(option_value)
                    - LENGTH( REPLACE ( option_value, ".", "") ) 
                ) / LENGTH(".")        
            ) < 3
        ');

        $sipd = $sipd->get();

        return $sipd;
    }

    public function unit()
    {
        // SIPD
        $sipd = $this->getSipdUnit();

        // SIMDA
        $simda = $this->getSimdaUnit();

        // Transform SIMDA
        $this->simdaTransformer($simda);

        // Check Unit
        $errorList = collect();
        $successList = collect();
        $sipd->each(function ($sipdData) use ($errorList, $successList) {
            $sipdData->kode_skpd_array = explode('.', $sipdData->kode_skpd);

            if (count($sipdData->kode_skpd_array) < 4) {
                $errorList->push($sipdData);
            } else {
                $simdaData = $this->showSimdaUnit($sipdData->kode_skpd);
                if ($simdaData) {
                    if ($sipdData->nama_skpd == $simdaData->nama_skpd && $sipdData->kode_skpd == $simdaData->kode_skpd) {
                        $successList->push($sipdData);
                    } else {
                        $errorList->push($sipdData);
                    }
                } else {
                    $errorList->push($sipdData);
                }
            }
        });

        // Return
        $simdaCount = $simda->count();
        $sipdCount = $sipd->count();
        $errorCount = $errorList->count();
        $successCount = $successList->count();

        return compact([
            'errorCount',
            'successCount',
            'errorList',
            'successList',
            'simdaCount',
            'sipdCount',
            'simda',
            'sipd',
        ]);
    }
}
