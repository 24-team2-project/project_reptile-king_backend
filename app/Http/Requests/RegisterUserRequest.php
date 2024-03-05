<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    protected $stopOnFirstFailure = true; // 유효성검사에서 실패하면 그 이후는 중단

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 이 값은 현재 유저가 저장이 가능한 지 검사하는 역할
        // 지금은 유저의 권한 개념이 없으므로 true
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string','max:255'],

            // 테스트용 : rfc, strict 사용
            // 'email' => ['required', 'string', 'max:255', 'unique:users,email', 'email:rfc,dns,strict,spoof'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email','email:rfc, strict'],
            
            // 최소 8자, 알파벳, 숫자 포함 + 특수문자, 대소문자
            'password' => ['required', Rules\Password::defaults()->mixedCase()->symbols() ],
            
            'nickname' => ['required', 'string', 'max:255', 'unique:users,nickname'],
            'address' => ['string'],
            'phone' => ['string'],
        ];
    }


}
