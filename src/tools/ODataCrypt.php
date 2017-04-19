<?php

class ODataCrypt
{
    const METHOD = 'aes-256-ctr';
    private static $vi;
    private static $key;
    
    public static function setupSession(){
        if (!isset($_SESSION["ODataCrypt_vi"])){
            $_SESSION["ODataCrypt_vi"] = ODataCrypt::generateVi();
        }
        
        ODataCrypt::setVi($_SESSION["ODataCrypt_vi"]);
        
        if (!isset($_SESSION["ODataCrypt_key"])){
            $_SESSION["ODataCrypt_key"] = openssl_random_pseudo_bytes(32);
        }
        
        ODataCrypt::setKey($_SESSION["ODataCrypt_key"]);
    }
    
    public static function generateVi(){
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        return openssl_random_pseudo_bytes($nonceSize);
    }
    
    public static function setVi($vi){
        ODataCrypt::$vi=$vi;
    }
    
    public static function setKey($key){
        ODataCrypt::$key=$key;
    }
    
    /**
     * Encrypts (but does not authenticate) a message
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded 
     * @return string (raw binary)
     */
    public static function encrypt($message, $key=null, $base64 = true)
    {
        if ($key==null)
            $key=ODataCrypt::$key;
        
        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            ODataCrypt::$vi
        );
        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($base64) {
            return base64_encode(ODataCrypt::$vi.$ciphertext);
        }
        return ODataCrypt::$vi.$ciphertext;
    }

    /**
     * Decrypts (but does not verify) a message
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    public static function decrypt($message, $key=null, $base64 = true)
    {
        if ($key==null)
            $key=ODataCrypt::$key;
        
        if ($base64) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext;
    }
}