<?php

namespace App\Http\Requests;

use App\Traits\Validate\ValidateTrait;
use Illuminate\Foundation\Http\FormRequest;

class RequestSendOtp extends FormRequest
{
    use ValidateTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return $this->a();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $rules = [
            'email' => 'required_without:mobile|email',
            'mobile' => 'required_without:email|digits:10',
            "userType"=>"nullable|string|in:Citizen",
            "otpType" => "nullable|string|in:Forgot Password,Register,Attach Holding,Update Mobile",
        ];
        return $rules;
    }
}
