<?php

class JWT {
    private $secretKey;
    private $algorithm;
    private $accessTokenExpiry;
    private $refreshTokenExpiry;
    
    public function __construct() {
        // Load from environment or use defaults
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production';
        $this->algorithm = 'HS256';
        $this->accessTokenExpiry = 3600; // 1 hour
        $this->refreshTokenExpiry = 604800; // 7 days
    }
    
    /**
     * Generate access token
     */
    public function generateAccessToken($userId, $username, $role) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode([
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + $this->accessTokenExpiry,
            'type' => 'access'
        ]);
        
        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secretKey, true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Generate refresh token
     */
    public function generateRefreshToken($userId) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode([
            'user_id' => $userId,
            'iat' => time(),
            'exp' => time() + $this->refreshTokenExpiry,
            'type' => 'refresh'
        ]);
        
        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secretKey, true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Verify access token
     */
    public function verifyToken($token) {
        try {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                return false;
            }
            
            $header = $this->base64UrlDecode($tokenParts[0]);
            $payload = $this->base64UrlDecode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];
            
            // Verify signature
            $base64Header = $this->base64UrlEncode($header);
            $base64Payload = $this->base64UrlEncode($payload);
            $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secretKey, true);
            $base64Signature = $this->base64UrlEncode($signature);
            
            if (!hash_equals($base64Signature, $signatureProvided)) {
                return false;
            }
            
            $payloadData = json_decode($payload);
            
            // Check if token is expired
            if ($payloadData->exp < time()) {
                return false;
            }
            
            // Check if it's an access token
            if (!isset($payloadData->type) || $payloadData->type !== 'access') {
                return false;
            }
            
            return $payloadData;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verify refresh token
     */
    public function verifyRefreshToken($token) {
        try {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                return false;
            }
            
            $header = $this->base64UrlDecode($tokenParts[0]);
            $payload = $this->base64UrlDecode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];
            
            // Verify signature
            $base64Header = $this->base64UrlEncode($header);
            $base64Payload = $this->base64UrlEncode($payload);
            $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secretKey, true);
            $base64Signature = $this->base64UrlEncode($signature);
            
            if (!hash_equals($base64Signature, $signatureProvided)) {
                return false;
            }
            
            $payloadData = json_decode($payload);
            
            // Check if token is expired
            if ($payloadData->exp < time()) {
                return false;
            }
            
            // Check if it's a refresh token
            if (!isset($payloadData->type) || $payloadData->type !== 'refresh') {
                return false;
            }
            
            return $payloadData;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Get token from authorization header
     */
    public static function getTokenFromHeader() {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }
        
        return $matches[1];
    }
}
?>