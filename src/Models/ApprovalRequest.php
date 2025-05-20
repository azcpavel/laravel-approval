<?php

namespace Exceptio\ApprovalPermission\Models;

use Illuminate\Database\Eloquent\Model;

use Exceptio\ApprovalPermission\Models\Approval;
use Exceptio\ApprovalPermission\Models\ApprovalRequestApproval;
use Exceptio\ApprovalPermission\Models\ApprovalRequestApprover;
use Exceptio\ApprovalPermission\Models\ApprovalRequestMappingField;

class ApprovalRequest extends Model
{
	protected $table="ex_approval_requests";
	protected $fillable=[
		'approval_id',
		'approvable_type',
		'approvable_id',
		'user_id',
		'approval_state',
		'completed'
	];

	public function approval(){
		return $this->belongsTo(Approval::class);
	}

	public function approvals(){
		return $this->hasMany(ApprovalRequestApproval::class);
	}

	public function approvers(){
		return $this->hasMany(ApprovalRequestApprover::class);
	}

	public function mappings(){
		return $this->hasMany(ApprovalRequestMappingField::class);
	}

	public function approvable(){
		return $this->morphTo();
	}

	public function currentLevel($returnItem = false){
		$levels = $this->approval->levels;
		$approvalRequest = $this;

		$current = $levels->filter(function($item) use($approvalRequest){
			return $item->level == $approvalRequest->approval_state;
		})->values();

		if($returnItem){
			if(count($current) > 0)
				return $current[0];
			return null;
		}

		if($this->completed == 1)
			return 'Completed';
		if($this->approval->on_create && $this->completed == 2)
			return 'Declined';
		if($this->approval->on_create && $this->completed == 3)
			return 'Send Back';
		if(!$this->approval->on_create && $this->completed == 2)
			return 'Rejected';
		else if($this->completed == 0 && count($current) > 0)
			return $current[0]->title;
		else
			return 'Pending';
	}

	/**
     * Details:
     * @param int $limit
     * @param int $offset
     * @param string $search
     * @param array $where
     * @param array $with
     * @param array $join
     * @param array $order_by
     * @param string $table_col_name
     * @param null $select
     * @param null $whereHas
     * @return array
     * @Author: Ahsan Zahid Chowdhury <azc.pavel@gmail.com>
     * @Date: 2021-11-20
     * @Time: 11:08 PM
     */

    public function getDataForDataTable($limit = 20, $offset = 0, $search = '', $where = [], $with = [], $join = [], $order_by = [], $table_col_name = '', $select = null, $whereHas = null, $whereHasType = ['*'], $user_selection = null){

        $totalData = $this::query();
        $filterData = $this::query();
		$totalCount = $this::query();

        if($limit > 0){
            $totalData->limit($limit)->offset($offset);
        }

        if(count($with) > 0){
            foreach ($with as $w) {
                $totalData->with($w);
            }
        }

        if(count($join) > 0){
            foreach ($join as $joinItem) {
            	$whereJ = null;
            	$nameJ = $joinItem[0];
            	$withJ = $joinItem[1];
            	$asJ = $joinItem[2];
            	if(isset($joinItem[3]))
            		$whereJ = $joinItem[3];

				$name_array = explode(" ", $nameJ);
				$name_as = end($name_array);
				if($name_as =='rev'){
					$totalData->leftJoin($name_array[0], $withJ, '=', $this->getTable().'.id')
					->selectRaw($asJ);
					$filterData->leftJoin($name_array[0], $withJ, '=', $this->getTable().'.id');
					$totalCount->leftJoin($name_array[0], $withJ, '=', $this->getTable().'.id');
				}else if($name_as =='rev_column'){
                    $totalData->leftJoin($name_array[0], $withJ, '=', $name_array[0].'.'.$name_array[1])
                    ->selectRaw($asJ);
                    $filterData->leftJoin($name_array[0], $withJ, '=', $name_array[0].'.'.$name_array[1]);
                    $totalCount->leftJoin($name_array[0], $withJ, '=', $name_array[0].'.'.$name_array[1]);
                }else if($name_as =='inner'){
                    $totalData->join($name_array[0], $withJ, '=', $name_array[0].'.id')
                    ->selectRaw($asJ);
                    $filterData->join($name_array[0], $withJ, '=', $name_array[0].'.id');
                    $totalCount->join($name_array[0], $withJ, '=', $name_array[0].'.id');
                }else if($name_as =='function'){
                    $totalData->join($name_array[0], $withJ)->selectRaw($asJ);
                    $filterData->join($name_array[0], $withJ);
                    $totalCount->join($name_array[0], $withJ);
                }
				else{
					$totalData->leftJoin($nameJ, $withJ, '=', $name_as.'.id')
					->selectRaw($asJ);
					$filterData->leftJoin($nameJ, $withJ, '=', $name_as.'.id');
					$totalCount->leftJoin($nameJ, $withJ, '=', $name_as.'.id');
				}

				if($whereJ && count($whereJ)>0){
                    foreach($whereJ as $keyWhereJ => $valueWhereJ){
                        $totalData->where([$valueWhereJ]);                        
                        $filterData->where([$valueWhereJ]);
                        $totalCount->where([$valueWhereJ]);
                    }
                }
			}

            if($select == null){
            	$totalData->selectRaw($this->getTable().'.*');
            	$filterData->selectRaw($this->getTable().'.*');
            }
        }

        if(strlen($search) > 0){
            $totalData->hasMorph('approvable', $whereHasType, '>=', 1, 'and', function($query) use($search,$whereHas) {
				$query->where(function($querySub) use($search,$whereHas){
					foreach ($whereHas as $keyS => $valueS) {
						if(strpos($valueS, ":") === false)					
							$querySub->orWhere($valueS, config('approval-config.db_like_operator'), "%$search%");
						else{
							$querySub->orWhereHas(explode(':',$valueS)[0], function ($querySubSub) use ($search,$valueS) {
						        $querySubSub->where(explode(':',$valueS)[1], config('approval-config.db_like_operator'), "%$search%");
						    });							
						}					
					}
				});				
			});

			$filterData->hasMorph('approvable', $whereHasType, '>=', 1, 'and', function($query) use($search,$whereHas) {
				$query->where(function($querySub) use($search,$whereHas){
					foreach ($whereHas as $keyS => $valueS) {
						if(strpos($valueS, ":") === false)				
							$querySub->orWhere($valueS, config('approval-config.db_like_operator'), "%$search%");
						else{
							$querySub->orWhereHas(explode(':',$valueS)[0], function ($querySubSub) use ($search,$valueS) {
						        $querySubSub->where(explode(':',$valueS)[1], config('approval-config.db_like_operator'), "%$search%");
						    });							
						}				
					}
				});				
			});
        }

        if($user_selection){        	
        	$user = auth()->user();
        	$totalData->whereExists(function ($query) use($user, $whereHasType){
                $query->select(\DB::raw(1))
                      ->from('ex_approval_level_users')
                      ->join('ex_approval_levels','ex_approval_levels.id','=','ex_approval_level_users.approval_level_id')
                      ->whereColumn('ex_approval_levels.approval_id', 'ex_approval_requests.approval_id')
                      ->where('ex_approval_level_users.user_id',$user->id);
                if(count($whereHasType)>0)
                      $query->where('ex_approval_requests.approvable_type',$whereHasType[0]);
            });
            $filterData->whereExists(function ($query) use($user, $whereHasType){
                $query->select(\DB::raw(1))
                      ->from('ex_approval_level_users')
                      ->join('ex_approval_levels','ex_approval_levels.id','=','ex_approval_level_users.approval_level_id')
                      ->whereColumn('ex_approval_levels.approval_id', 'ex_approval_requests.approval_id')
                      ->where('ex_approval_level_users.user_id',$user->id);
                if(count($whereHasType)>0)
                      $query->where('ex_approval_requests.approvable_type',$whereHasType[0]);
            });
            $totalCount->whereExists(function ($query) use($user, $whereHasType){
                $query->select(\DB::raw(1))
                      ->from('ex_approval_level_users')
                      ->join('ex_approval_levels','ex_approval_levels.id','=','ex_approval_level_users.approval_level_id')
                      ->whereColumn('ex_approval_levels.approval_id', 'ex_approval_requests.approval_id')
                      ->where('ex_approval_level_users.user_id',$user->id);
                if(count($whereHasType)>0)
                      $query->where('ex_approval_requests.approvable_type',$whereHasType[0]);
            });
        	foreach($user_selection as $usKey => $usValue){
        		if($usValue->type == 'model'){
        			$totalData->hasMorph('approvable', $whereHasType, '>=', 1, 'or', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});

        			$filterData->hasMorph('approvable', $whereHasType, '>=', 1, 'or', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});

					$totalCount->hasMorph('approvable', $whereHasType, '>=', 1, 'or', function($query) use($user, $usValue) {
						foreach($usValue->items as $usValueKey => $usValueValue){
							foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$user->$usValueValueValue);
							}
	        			}									
					});
        		}if($usValue->type == 'model_collection'){
        			$totalData->hasMorph('approvable', $whereHasType, '>=', 1, 'or', function($query) use($user, $usValue) {
                        foreach($usValue->items as $usValueKey => $usValueValue){
                            foreach($usValueValue as $usValueValueKey => $usValueValueValue){
                                $user_relation = $usValueValueValue->relation;
                                if(!$user->$user_relation || count($user->$user_relation) == 0)
                                    $query->where($usValueValueKey,-9999);
                                else
                                    $query->whereIn($usValueValueKey,$user->$user_relation->pluck($usValueValueValue->property)->toArray());
                                
                            }
                        }                                   
                    });

                    $filterData->hasMorph('approvable', $whereHasType, '>=', 1, 'or', function($query) use($user, $usValue) {
                        foreach($usValue->items as $usValueKey => $usValueValue){
                            foreach($usValueValue as $usValueValueKey => $usValueValueValue){
                                $user_relation = $usValueValueValue->relation;
                                if(!$user->$user_relation || count($user->$user_relation) == 0)
                                    $query->where($usValueValueKey,-9999);
                                else
                                    $query->whereIn($usValueValueKey,$user->$user_relation->pluck($usValueValueValue->property)->toArray());
                                
                            }
                        }                                   
                    });

                    $totalCount->hasMorph('approvable', $whereHasType, '>=', 1, 'or', function($query) use($user, $usValue) {
                        foreach($usValue->items as $usValueKey => $usValueValue){
                            foreach($usValueValue as $usValueValueKey => $usValueValueValue){
                                $user_relation = $usValueValueValue->relation;
                                if(!$user->$user_relation || count($user->$user_relation) == 0)
                                    $query->where($usValueValueKey,-9999);
                                else
                                    $query->whereIn($usValueValueKey,$user->$user_relation->pluck($usValueValueValue->property)->toArray());
                                
                            }
                        }                                   
                    });
        		}else if($usValue->type == 'value'){
        			foreach($usValue->items as $usValueKey => $usValueValue){
        				$totalData->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			           	});

        				$filterData->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			            });

			            $totalCount->whereExists(function ($query) use($usValueKey, $usValueValue, $user){
			               $query->select(\DB::raw(1))
			                     ->from(config('approval-config.user-table'))
			                     ->where('id',$user->id);
			                foreach($usValueValue as $usValueValueKey => $usValueValueValue){
	        					$query->where($usValueValueKey,$usValueValueValue);
							}
			            });
        			}
        		}
        	}
        }

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
