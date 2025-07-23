<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class SignatureHelper
{
    public static function generateSignatureFromRaw($timestamp, string $rawBody): string
    {
        $secret = 'verysecret'; // atau pakai env('SIGNATURE_SECRET')
        $payload = $timestamp . $rawBody;
        return hash_hmac('sha256', $payload, $secret);
    }

    public static function isValid(Request $request): bool
    {
        $timestamp = $request->header('X-Timestamp');
        $signature = $request->header('X-Signature');
        $rawBody = $request->getContent(); // Ambil raw string, bukan array

        $expectedSignature = self::generateSignatureFromRaw($timestamp, $rawBody);

        \Log::info("Expected: $expectedSignature");
        \Log::info("Provided: $signature");

        return hash_equals($expectedSignature, $signature);
    }
}
