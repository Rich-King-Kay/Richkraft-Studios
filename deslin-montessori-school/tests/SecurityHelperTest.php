<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the SecurityHelper class.
 *
 * SecurityHelper is a collection of static utility methods (password hashing,
 * validation, sanitization, CSRF and role helpers) with no database
 * dependency, so it is a good candidate for straightforward unit testing.
 */
class SecurityHelperTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset session-backed state between tests.
        $_SESSION = [];
        $_SERVER = array_diff_key($_SERVER, array_flip([
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
            'HTTP_USER_AGENT',
        ]));
    }

    public function testHashPasswordProducesVerifiableBcryptHash(): void
    {
        $hash = SecurityHelper::hashPassword('S3cret!Pass');

        $this->assertIsString($hash);
        $this->assertNotSame('S3cret!Pass', $hash);
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(SecurityHelper::verifyPassword('S3cret!Pass', $hash));
    }

    public function testVerifyPasswordFailsForWrongPassword(): void
    {
        $hash = SecurityHelper::hashPassword('correct-horse');

        $this->assertFalse(SecurityHelper::verifyPassword('wrong-password', $hash));
    }

    public function testGenerateTokenReturnsHexOfExpectedLength(): void
    {
        $token = SecurityHelper::generateToken(16);

        // bin2hex doubles the byte length.
        $this->assertSame(32, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $token);
    }

    public function testGenerateTokenProducesUniqueValues(): void
    {
        $this->assertNotSame(
            SecurityHelper::generateToken(),
            SecurityHelper::generateToken()
        );
    }

    public function testSanitizeInputTrimsAndEscapes(): void
    {
        $result = SecurityHelper::sanitizeInput('  <script>alert("x")</script>  ');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertSame($result, trim($result));
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidateEmailAcceptsValidAddresses(string $email): void
    {
        $this->assertTrue(SecurityHelper::validateEmail($email));
    }

    public static function validEmailProvider(): array
    {
        return [
            ['user@example.com'],
            ['first.last@sub.domain.org'],
            ['name+tag@example.co.uk'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testValidateEmailRejectsInvalidAddresses(string $email): void
    {
        $this->assertFalse(SecurityHelper::validateEmail($email));
    }

    public static function invalidEmailProvider(): array
    {
        return [
            ['not-an-email'],
            ['missing@tld'],
            ['@example.com'],
            [''],
        ];
    }

    public function testValidatePhoneNumberAcceptsValidNumbers(): void
    {
        $this->assertTrue(SecurityHelper::validatePhoneNumber('+234-801-234-5678'));
        $this->assertTrue(SecurityHelper::validatePhoneNumber('08012345678'));
    }

    public function testValidatePhoneNumberRejectsShortNumbers(): void
    {
        $this->assertFalse(SecurityHelper::validatePhoneNumber('12345'));
    }

    public function testValidatePasswordStrengthAcceptsStrongPassword(): void
    {
        $result = SecurityHelper::validatePasswordStrength('Str0ng!Pass');

        $this->assertTrue($result['valid']);
        $this->assertSame('Password is strong', $result['message']);
    }

    /**
     * @dataProvider weakPasswordProvider
     */
    public function testValidatePasswordStrengthRejectsWeakPasswords(string $password, string $expectedFragment): void
    {
        $result = SecurityHelper::validatePasswordStrength($password);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString($expectedFragment, $result['message']);
    }

    public static function weakPasswordProvider(): array
    {
        return [
            'too short'        => ['Ab1!', 'at least'],
            'no uppercase'     => ['weak1pass!', 'uppercase'],
            'no lowercase'     => ['WEAK1PASS!', 'lowercase'],
            'no number'        => ['WeakPass!', 'number'],
            'no special char'  => ['WeakPass1', 'special character'],
        ];
    }

    public function testCsrfTokenGenerationAndVerification(): void
    {
        $token = SecurityHelper::generateCSRFToken();

        $this->assertNotEmpty($token);
        // Calling again returns the same token for the session.
        $this->assertSame($token, SecurityHelper::generateCSRFToken());
        $this->assertTrue(SecurityHelper::verifyCSRFToken($token));
        $this->assertFalse(SecurityHelper::verifyCSRFToken('bogus-token'));
    }

    public function testVerifyCsrfTokenReturnsFalseWhenNoTokenSet(): void
    {
        unset($_SESSION['csrf_token']);

        $this->assertFalse(SecurityHelper::verifyCSRFToken('anything'));
    }

    public function testIsLoggedInReflectsSessionState(): void
    {
        $this->assertFalse(SecurityHelper::isLoggedIn());

        $_SESSION['user_id'] = 42;
        $this->assertTrue(SecurityHelper::isLoggedIn());
    }

    public function testHasRoleChecksSessionRole(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'admin';

        $this->assertTrue(SecurityHelper::hasRole('admin'));
        $this->assertFalse(SecurityHelper::hasRole('teacher'));
    }

    public function testHasRoleReturnsFalseWhenNotLoggedIn(): void
    {
        $_SESSION['user_role'] = 'admin';

        $this->assertFalse(SecurityHelper::hasRole('admin'));
    }

    public function testHasAnyRoleChecksMembership(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'teacher';

        $this->assertTrue(SecurityHelper::hasAnyRole(['admin', 'teacher']));
        $this->assertFalse(SecurityHelper::hasAnyRole(['admin', 'accountant']));
    }

    public function testHasAnyRoleReturnsFalseWhenNotLoggedIn(): void
    {
        $this->assertFalse(SecurityHelper::hasAnyRole(['admin', 'teacher']));
    }

    public function testGetClientIpPrefersClientIpHeader(): void
    {
        $_SERVER['HTTP_CLIENT_IP'] = '203.0.113.5';
        $_SERVER['REMOTE_ADDR'] = '198.51.100.9';

        $this->assertSame('203.0.113.5', SecurityHelper::getClientIP());
    }

    public function testGetClientIpFallsBackToRemoteAddr(): void
    {
        $_SERVER['REMOTE_ADDR'] = '198.51.100.9';

        $this->assertSame('198.51.100.9', SecurityHelper::getClientIP());
    }

    public function testGetClientIpReturnsFalseForInvalidIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = 'not-an-ip';

        $this->assertFalse(SecurityHelper::getClientIP());
    }

    public function testGetUserAgentReturnsUnknownWhenMissing(): void
    {
        $this->assertSame('Unknown', SecurityHelper::getUserAgent());
    }

    public function testGetUserAgentReturnsHeaderValue(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit-Agent/1.0';

        $this->assertSame('PHPUnit-Agent/1.0', SecurityHelper::getUserAgent());
    }
}
