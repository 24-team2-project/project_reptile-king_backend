<x-mail::message>
# Introduction

Authentication code

인증코드 : {{ $auth_code }}

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
