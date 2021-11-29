<?php

namespace Exceptio\ApprovalPermission\Models;
use Auth;

trait ModelCommonMethodTrait
{
    /**
     * Details:
     * @param int $limit
     * @param int $offset
     * @param array $search
     * @param array $where
     * @param array $with
     * @param array $join
     * @param array $order_by
     * @param string $table_col_name
     * @param null $select
     * @return array
     * @Author: Ahsan Zahid Chowdhury <azc.pavel@gmail.com>
     * @Date: 2021-11-15
     * @Time: 11:08 AM
     */

    public function getDataForDataTable($limit = 20, $offset = 0, $search = [], $where = [], $with = [], $join = [], $order_by = [], $table_col_name = '', $select = null){

        $totalData = $this::query();
        $filterData = $this::query();
		$totalCount = $this::query();

        if(count($where) > 0){
            foreach ($where as $keyW => $valueW) {
				if(strpos($keyW, ' IN') !== false){
                    $keyW = str_replace(' IN', '', $keyW);
                    $totalData->whereIn($keyW, $valueW);
                    $filterData->whereIn($keyW, $valueW);
                    $totalCount->whereIn($keyW, $valueW);
                }else if(strpos($keyW, ' NOTIN') !== false){
                    $keyW = str_replace(' NOTIN', '', $keyW);
                    $totalData->whereNotIn($keyW, $valueW);
                    $filterData->whereNotIn($keyW, $valueW);
                    $totalCount->whereNotIn($keyW, $valueW);
                }else if(is_array($valueW)){
                    $totalData->where([$valueW]);
                    $filterData->where([$valueW]);
                    $totalCount->where([$valueW]);
                }else if(strpos($keyW, ' and') === false){
                    if(strpos($keyW, ' NOTEQ') !== false){
                        $keyW = str_replace(' NOTEQ', '', $keyW);
                        $totalData->orWhere($keyW, '!=', $valueW);
                        $filterData->orWhere($keyW, '!=',  $valueW);
                        $totalCount->orWhere($keyW, '!=', $valueW);
                    }
                    else{
                        $totalData->orWhere($keyW, $valueW);
                        $filterData->orWhere($keyW, $valueW);
                        $totalCount->orWhere($keyW, $valueW);
                    }
                }
                else{
                    $keyW = str_replace(' and', '', $keyW);
                    if(strpos($keyW, ' NOTEQ') !== false){
                        $keyW = str_replace(' NOTEQ', '', $keyW);
                        $totalData->where($keyW, '!=', $valueW);
                        $filterData->where($keyW, '!=',  $valueW);
                        $totalCount->where($keyW, '!=', $valueW);
                    }
                    else{
                        $totalData->where($keyW, $valueW);
                        $filterData->where($keyW, $valueW);
                        $totalCount->where($keyW, $valueW);
                    }
                }
			}
        }


        if($limit > 0){
            $totalData->limit($limit)->offset($offset);
        }

        if(count($with) > 0){
            foreach ($with as $w) {
                $totalData->with($w);
            }
        }

        if(count($join) > 0){
            foreach ($join as list($nameJ, $withJ, $asJ)) {
				$name_array = explode(" ", $nameJ);
				$name_as = end($name_array);
				if($name_as =='rev'){
					$totalData->leftJoin($name_array[0], $withJ, '=', $this->getTable().'.id')
					->selectRaw($asJ);
					$filterData->leftJoin($name_array[0], $withJ, '=', $this->getTable().'.id');
					$totalCount->leftJoin($name_array[0], $withJ, '=', $this->getTable().'.id');
				}else if($name_as =='inner'){
                    $totalData->join($name_array[0], $withJ, '=', $name_array[0].'.id')
                    ->selectRaw($asJ);
                    $filterData->join($name_array[0], $withJ, '=', $name_array[0].'.id');
                    $totalCount->join($name_array[0], $withJ, '=', $name_array[0].'.id');
                }
				else{
					$totalData->leftJoin($nameJ, $withJ, '=', $name_as.'.id')
					->selectRaw($asJ);
					$filterData->leftJoin($nameJ, $withJ, '=', $name_as.'.id');
					$totalCount->leftJoin($nameJ, $withJ, '=', $name_as.'.id');
				}
			}

            if($select == null){
            	$totalData->selectRaw($this->getTable().'.*');
            	$filterData->selectRaw($this->getTable().'.*');
            }
        }

        if(count($search) > 0){
            $totalData->where(function($totalData) use($search) {
				foreach ($search as $keyS => $valueS) {
					if(strpos($keyS, ' and') === false){
						$totalData->orWhere($keyS, 'like', "%$valueS%");
					}
					else{
						$keyS = str_replace(' and', '', $keyS);
						$totalData->where($keyS, $valueS);
					}
				}
			});

			$filterData->where(function($filterData) use($search) {
				foreach ($search as $keyS => $valueS) {
					$filterData->orWhere($keyS, 'like', "%$valueS%");
				}
			});
        }

        if($select != null){
        	$totalData->selectRaw($select);
        	$filterData->selectRaw($select);
        }

        if (count($order_by) > 0) {
			foreach ($order_by as $col => $by) {
				$totalData->orderBy($col, $by);
			}
		} else {
			$totalData->orderBy($this->getTable() . '.id', 'DESC');
        }

        return [
            'data' => $totalData->get(),
            'draw'      => request()->input('draw'), //prevent Cross Site Scripting (XSS) attacks. https://datatables.net/manual/server-side
            'recordsTotal'  => $totalCount->count(),
            'recordsFiltered'   => $filterData->count(),
        ];
    }

}
