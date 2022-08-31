<?php

namespace App\Services;


use App\Models\User;
use App\Models\UsersInformation;
use Illuminate\Support\Facades\DB;


class  IdentityService extends BaseService
{
    /**
     * @param string $role
     * @param User $user
     * @return bool
     * @throws \Throwable
     */
    public function update(string $role, User $user): bool
    {
        DB::beginTransaction();
         $res1 = tap(UsersInformation::firstOrCreate([
            'role' => $role,
            'user_id' => $user->id,
            'uuid' => genUid($user->id, $role)
        ]), static function (UsersInformation $usersInformation): void {
             if(!$usersInformation->exists) {
                 $usersInformation->add_time = time();
             }else {
                 $usersInformation->update_time = time();
             }
        });
         $user->update_time = time();
         $user->current_role = $role;
        $res2 = $user->save();
        if(!$res1 || !$res2) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

}
