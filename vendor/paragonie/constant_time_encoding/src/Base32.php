<?php

namespace ParagonIE\ConstantTime;

class Base32 {
    const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

    public static function encodeUpper($str) {
        $str = (string) $str;
        $str = str_split($str);
        $r = '';
        
        $remainder = count($str) % 5;
        $remainderBits = $remainder * 8;
        $fullSteps = (count($str) - $remainder) / 5;

        for ($i = 0; $i < $fullSteps; $i++) {
            $chunk = array_slice($str, $i * 5, 5);
            $r .= self::encode5Bytes($chunk);
        }

        if ($remainder > 0) {
            $chunk = array_slice($str, $fullSteps * 5);
            $r .= self::encodeRemainder($chunk, $remainderBits);
        }

        return $r;
    }

    public static function decodeUpper($str) {
        $str = (string) $str;
        $str = str_replace('=', '', $str);
        $str = str_split($str);
        $r = '';
        
        $remainder = count($str) % 8;
        $remainderBits = $remainder * 5;
        $fullSteps = (count($str) - $remainder) / 8;

        for ($i = 0; $i < $fullSteps; $i++) {
            $chunk = array_slice($str, $i * 8, 8);
            $r .= self::decode8Chars($chunk);
        }

        if ($remainder > 0) {
            $chunk = array_slice($str, $fullSteps * 8);
            $r .= self::decodeRemainder($chunk, $remainderBits);
        }

        return $r;
    }

    private static function encode5Bytes($bytes) {
        $binary = '';
        foreach ($bytes as $byte) {
            $binary .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        
        $chunks = str_split($binary, 5);
        $result = '';
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= self::CHARSET[bindec($chunk)];
        }
        
        return $result;
    }

    private static function encodeRemainder($bytes, $bits) {
        $binary = '';
        foreach ($bytes as $byte) {
            $binary .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        
        $chunks = str_split($binary, 5);
        $result = '';
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= self::CHARSET[bindec($chunk)];
        }
        
        $padding = ceil(strlen($result) / 8) * 8 - strlen($result);
        return $result . str_repeat('=', $padding);
    }

    private static function decode8Chars($chars) {
        $binary = '';
        foreach ($chars as $char) {
            $val = strpos(self::CHARSET, $char);
            if ($val === false) {
                $val = 0;
            }
            $binary .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }
        
        $chunks = str_split($binary, 8);
        $result = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $result .= chr(bindec($chunk));
            }
        }
        
        return $result;
    }

    private static function decodeRemainder($chars, $bits) {
        $binary = '';
        foreach ($chars as $char) {
            $val = strpos(self::CHARSET, $char);
            if ($val === false) {
                $val = 0;
            }
            $binary .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }
        
        $binary = substr($binary, 0, floor($bits / 8) * 8);
        $chunks = str_split($binary, 8);
        
        $result = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $result .= chr(bindec($chunk));
            }
        }
        
        return $result;
    }
} 