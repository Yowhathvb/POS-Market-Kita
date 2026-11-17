<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthHelper
{
    /**
     * Generate guaranteed 12-character alphanumeric string (letters + digits).
     */
    public static function generateAlphanumeric12(): string
    {
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $combined = $letters . $digits;

        // Ensure at least one letter and at least one digit
        $chars = [
            $letters[random_int(0, strlen($letters) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
        ];

        for ($i = 2; $i < 12; $i++) {
            $chars[] = $combined[random_int(0, strlen($combined) - 1)];
        }

        shuffle($chars);
        return implode('', $chars);
    }

    /**
     * Generate the login key:
     * 12 random alphanumeric characters + username, then hashed.
     *
     * Returns an array with raw string, 12-char random string, and the hashed string.
     */
    public static function generateLoginKey(string $username): array
    {
        $random12 = self::generateAlphanumeric12();
        $raw = $random12 . $username;
        $hashed = Hash::make($raw);

        return [
            'random_12' => $random12,
            'username' => $username,
            'raw_key' => $raw,
            'hashed_key' => $hashed,
        ];
    }

    /**
     * Generate a unique id_users string with prefix POS- and random letters + numbers (length > 5).
     * Example: POS-K8A9B2C4 (8 characters after POS-).
     */
    public static function generateUserId(int $randomLength = 8): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $combined = $letters . $digits;

        do {
            $random = '';
            // Ensure at least one letter and at least one digit
            $chars = [
                $letters[random_int(0, strlen($letters) - 1)],
                $digits[random_int(0, strlen($digits) - 1)],
            ];

            for ($i = 2; $i < $randomLength; $i++) {
                $chars[] = $combined[random_int(0, strlen($combined) - 1)];
            }

            shuffle($chars);
            $id = 'POS-' . implode('', $chars);
        } while (User::where('id_users', $id)->exists());

        return $id;
    }

    /**
     * Parse and normalize the JSON value from array or string.
     */
    public static function parseJsonValue(mixed $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
