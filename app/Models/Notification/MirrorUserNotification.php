<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MirrorUserNotification extends Model
{
    use HasFactory;
    protected $updated_at = false;
    protected $guarded = [];

    /**
     * | Get notification of logged in user
     */
    public function notificationByUserId()
    {
        return MirrorUserNotification::select('*', DB::raw("Replace(category, ' ', '_') AS category"))
            ->where('status', 1)
            ->orderByDesc('id');
    }

    /**
     * | Add Notifications 
     */
    public function addNotification($req)
    {
        $req = $req->toarray();
        MirrorUserNotification::create($req);
    }

    /**
     * | Add Notifications 
     */
    public function editNotification($req)
    {
        $req = $req->toarray();
        MirrorUserNotification::update($req);
    }
}
