<?php

namespace App\Models;

use App\Repository\Property\Concrete\PropertyBifurcation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use App\MicroServices\DocUpload;

class CustomDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getCustomDetails($request)
    {
        $docUpload = new DocUpload;
        $customDetails = CustomDetail::select(
            'id',
            'ref_id',
            'ref_type',
            'doc_ref_no as reference_no',
            'doc_unique_id',
            'remarks',
            'type',
            'created_at as date',
            'ref_type as customFor'
        )
            ->where('ref_id', $request->applicationId)
            ->where('ref_type', trim(strtoupper($request->customFor)))
            ->orderByDesc('id')
            ->get();

        $customDetails = $customDetails->map(function ($val) use ($docUpload) {
            $docResponse =  $docUpload->getSingleDocUrl($val);
            $val->docUrl =  $docResponse['doc_path'] ?? null;
            return $val;
        });
        return $customDetails;
    }

    //post custom details
    public function postCustomDetails($request)
    {
        $customFor = trim(strtoupper($request->customFor));
        $path = Config::get('constants.CUSTOM_RELATIVE_PATH');

        $customDetails = new CustomDetail;
        $filename = NULL;

        if ($file = $request->file('document')) {
            $filename = time() .  '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);
        }

        $customDetails = new CustomDetail;
        if ($customFor == 'PROPERTY-CONCESSION') {
            $customDetails->ref_type = 'PROPERTY-CONCESSION';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'SAF') {
            $customDetails->ref_type = 'SAF';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'PROPERTY-OBJECTION') {
            $customDetails->ref_type = 'PROPERTY-OBJECTION';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'PROPERTY-HARVESTING') {
            $customDetails->ref_type = 'PROPERTY-HARVESTING';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'WATER') {
            $customDetails->ref_type = 'WATER';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'TRADE') {
            $customDetails->ref_type = 'TRADE';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'PROPERTY DEACTIVATION') {
            $customDetails->ref_type = 'PROPERTY DEACTIVATION';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'GBSAF') {
            $customDetails->ref_type = 'GBSAF';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'SELF') {
            $customDetails->ref_type = 'SELF';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'MOVABLE') {
            $customDetails->ref_type = 'MOVABLE';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'PRIVATE') {
            $customDetails->ref_type = 'PRIVATE';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'AGENCY') {
            $customDetails->ref_type = 'AGENCY';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'HOARDING') {
            $customDetails->ref_type = 'HOARDING';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'BANQUET') {
            $customDetails->ref_type = 'BANQUET';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'LODGE') {
            $customDetails->ref_type = 'LODGE';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'HOSTEL') {
            $customDetails->ref_type = 'HOSTEL';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }

        if ($customFor == 'DHARAMSHALA') {
            $customDetails->ref_type = 'DHARAMSHALA';
            $this->saveCustomDetail($request, $filename, $customDetails, $path);
            $customDetails->save();
        }
    }

    // save custom details
    public function saveCustomDetail($request)
    {
        CustomDetail::create($request);
    }
}
