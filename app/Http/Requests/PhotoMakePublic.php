<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PhotoMakePublic extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return 
        [
            'photoID'     =>  'required|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
        [

            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()

        ]));

    }
    protected $stopOnFirstFailure = true;
}
