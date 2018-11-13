<?php

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha512;

define('JWT_ISSUER', 'https://battre.infizi.com');
define('JWT_ID', '928rgybf02');
define('JWT_PRIVATE_KEY', 'fib298ofbu028pfbFE2ouip');

class JWT
{
    public static function createToken($Claims)
    {
        $Builder = new Builder();
        $Builder->setIssuer(JWT_ISSUER) // Configures the issuer (iss claim)
                            ->setAudience(JWT_ISSUE) // Configures the audience (aud claim)
                            ->setId(JWT_ID, true) // Configures the id (jti claim), replicating as a header item
                            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                            //->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
                            /*->setExpiration(time() + 3600)*/; // Configures the expiration time of the token (exp claim)

        foreach ($Claims as $Key=>$Value) {
            $Builder->set($Key, $Value); // Configures claims
        }

        $Signature = new Sha512();
        $Builder->sign($Signature, JWT_PRIVATE_KEY);

        return $Builder->getToken(); // Retrieves the generated token
    }

    public static function validateToken($tokenstr)
    {
        try {
            $Parser = new Parser();
            $Token = $Parser->parse((string) $tokenstr); // Parses from a string

            $Verification = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
            $Verification->setIssuer(JWT_ISSUER);
            $Verification->setAudience(JWT_ISSUER);
            $Verification->setId(JWT_ID);
            $Verification->setCurrentTime(time());

            $Signature = new Sha512();

            if ($Token->verify($Signature, JWT_PRIVATE_KEY)) {
                return $Token;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getTokenClaims($token)
    {
        if (validateToken($token) === false) {
            return [];
        }

        $Parser = new Parser();
        $Token = $Parser->parse((string) $token); // Parses from a string

        $token->getHeaders(); // Retrieves the token header
        return $token->getClaims(); // Retrieves the token claims
    }
}
