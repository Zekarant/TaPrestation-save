<?php

namespace App\Services;

/**
 * Service de gestion des HashIds pour encoder/décoder les IDs
 * Sécurise les URLs en masquant les IDs réels
 */
class HashIdService
{
    private string $salt;
    private int $minLength;
    private string $alphabet;
    
    // Séparateurs pour l'algorithme HashIds
    private string $seps = 'cfhistuCFHISTU';
    private string $guards;
    
    public function __construct()
    {
        // Utiliser des valeurs par défaut sans config() au cas où elle n'est pas encore chargée
        $this->salt = env('HASHIDS_SALT', 'TaPrestation-Secure-Salt-2024');
        $this->minLength = 10;
        $this->alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        
        $this->initialize();
    }
    
    private function initialize(): void
    {
        // Shuffle alphabet based on salt
        $this->alphabet = $this->shuffle($this->alphabet, $this->salt);
        
        // Setup separators
        $this->seps = $this->shuffle($this->seps, $this->salt);
        
        // Setup guards
        $guardCount = (int) ceil(strlen($this->alphabet) / 12);
        $this->guards = substr($this->alphabet, 0, $guardCount);
        $this->alphabet = substr($this->alphabet, $guardCount);
    }
    
    /**
     * Encode un ID en hash
     */
    public function encode(int $id): string
    {
        if ($id < 0) {
            return '';
        }
        
        return $this->doEncode([$id]);
    }
    
    /**
     * Decode un hash en ID
     */
    public function decode(string $hash): ?int
    {
        if (empty($hash)) {
            return null;
        }
        
        $result = $this->doDecode($hash);
        
        return !empty($result) ? (int) $result[0] : null;
    }
    
    /**
     * Encode plusieurs IDs
     */
    public function encodeMany(array $ids): string
    {
        $ids = array_map('intval', $ids);
        return $this->doEncode($ids);
    }
    
    /**
     * Decode plusieurs IDs
     */
    public function decodeMany(string $hash): array
    {
        return $this->doDecode($hash);
    }
    
    private function doEncode(array $numbers): string
    {
        if (empty($numbers)) {
            return '';
        }
        
        $alphabet = $this->alphabet;
        $numbersSize = count($numbers);
        $numbersHashInt = 0;
        
        foreach ($numbers as $i => $number) {
            $numbersHashInt += ($number % ($i + 100));
        }
        
        $lottery = $alphabet[$numbersHashInt % strlen($alphabet)];
        $ret = $lottery;
        
        foreach ($numbers as $i => $number) {
            $alphabet = $this->shuffle($alphabet, substr($lottery . $this->salt . $alphabet, 0, strlen($alphabet)));
            $last = $this->hash($number, $alphabet);
            $ret .= $last;
            
            if ($i + 1 < $numbersSize) {
                $number %= (ord($last) + $i);
                $sepsIndex = $number % strlen($this->seps);
                $ret .= $this->seps[$sepsIndex];
            }
        }
        
        if (strlen($ret) < $this->minLength) {
            $guardIndex = ($numbersHashInt + ord($ret[0])) % strlen($this->guards);
            $guard = $this->guards[$guardIndex];
            $ret = $guard . $ret;
            
            if (strlen($ret) < $this->minLength) {
                $guardIndex = ($numbersHashInt + ord($ret[2])) % strlen($this->guards);
                $guard = $this->guards[$guardIndex];
                $ret .= $guard;
            }
        }
        
        $halfLength = (int) (strlen($alphabet) / 2);
        while (strlen($ret) < $this->minLength) {
            $alphabet = $this->shuffle($alphabet, $alphabet);
            $ret = substr($alphabet, $halfLength) . $ret . substr($alphabet, 0, $halfLength);
            
            $excess = strlen($ret) - $this->minLength;
            if ($excess > 0) {
                $ret = substr($ret, (int) ($excess / 2), $this->minLength);
            }
        }
        
        return $ret;
    }
    
    private function doDecode(string $hash): array
    {
        $ret = [];
        
        $hashBreakdown = str_replace(str_split($this->guards), ' ', $hash);
        $hashArray = explode(' ', $hashBreakdown);
        
        $i = count($hashArray) === 3 || count($hashArray) === 2 ? 1 : 0;
        
        if (isset($hashArray[$i])) {
            $hashBreakdown = $hashArray[$i];
            
            if (!empty($hashBreakdown)) {
                $lottery = $hashBreakdown[0];
                $hashBreakdown = substr($hashBreakdown, 1);
                $hashBreakdown = str_replace(str_split($this->seps), ' ', $hashBreakdown);
                $hashArray = explode(' ', $hashBreakdown);
                
                $alphabet = $this->alphabet;
                
                foreach ($hashArray as $subHash) {
                    $alphabet = $this->shuffle($alphabet, substr($lottery . $this->salt . $alphabet, 0, strlen($alphabet)));
                    $ret[] = $this->unhash($subHash, $alphabet);
                }
            }
        }
        
        // Verify
        if ($this->doEncode($ret) !== $hash) {
            return [];
        }
        
        return $ret;
    }
    
    private function shuffle(string $alphabet, string $salt): string
    {
        if (empty($salt)) {
            return $alphabet;
        }
        
        $saltLength = strlen($salt);
        $alphabetArray = str_split($alphabet);
        
        for ($i = strlen($alphabet) - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {
            $v %= $saltLength;
            $p += $int = ord($salt[$v]);
            $j = ($int + $v + $p) % $i;
            
            $temp = $alphabetArray[$j];
            $alphabetArray[$j] = $alphabetArray[$i];
            $alphabetArray[$i] = $temp;
        }
        
        return implode('', $alphabetArray);
    }
    
    private function hash(int $input, string $alphabet): string
    {
        $hash = '';
        $alphabetLength = strlen($alphabet);
        
        do {
            $hash = $alphabet[$input % $alphabetLength] . $hash;
            $input = (int) ($input / $alphabetLength);
        } while ($input);
        
        return $hash;
    }
    
    private function unhash(string $input, string $alphabet): int
    {
        $number = 0;
        $inputLength = strlen($input);
        $alphabetLength = strlen($alphabet);
        
        for ($i = 0; $i < $inputLength; $i++) {
            $pos = strpos($alphabet, $input[$i]);
            $number += $pos * pow($alphabetLength, $inputLength - $i - 1);
        }
        
        return (int) $number;
    }
}
