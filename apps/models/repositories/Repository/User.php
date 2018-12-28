<?php

namespace Modules\Models\Repositories\Repository;

use Modules\Models\Entities\User as EntityUser;

class User
{
    public function getLast()
    {
        return EntityUser::query()
//            ->columns('count(*),name')
//            ->columns('name,min(iduser)')
//            ->columns('name,max(iduser)')
//            ->where('2=2')
//            ->orderBy('iduser asc')
//            ->groupBy('email')
//            ->limit(2, 0)//number,offset
//            ->having('iduser>0')
            ->execute();
    }
}
