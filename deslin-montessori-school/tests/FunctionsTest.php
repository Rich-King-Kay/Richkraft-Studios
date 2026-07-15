<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pure helper functions declared in includes/functions.php.
 *
 * Only the functions that do not require a database connection are exercised
 * here (formatting, role/status helpers, text and permission utilities).
 */
class FunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testFormatDate(): void
    {
        $this->assertSame('05 Jan 2025', formatDate('2025-01-05'));
    }

    public function testFormatDateTime(): void
    {
        $this->assertSame('05 Jan 2025 14:30', formatDateTime('2025-01-05 14:30:00'));
    }

    public function testGetCurrentAcademicYearReturnsConfiguredValue(): void
    {
        $this->assertSame(ACADEMIC_YEAR, getCurrentAcademicYear());
    }

    /**
     * @dataProvider roleDisplayProvider
     */
    public function testGetRoleDisplayName(string $role, string $expected): void
    {
        $this->assertSame($expected, getRoleDisplayName($role));
    }

    public static function roleDisplayProvider(): array
    {
        return [
            ['admin', 'Administrator'],
            ['teacher', 'Teacher'],
            ['accountant', 'Accountant'],
            ['staff', 'Staff'],
            // Unknown roles fall back to ucfirst().
            ['guardian', 'Guardian'],
        ];
    }

    /**
     * @dataProvider statusBadgeProvider
     */
    public function testGetStatusBadgeClass(string $status, string $expected): void
    {
        $this->assertSame($expected, getStatusBadgeClass($status));
    }

    public static function statusBadgeProvider(): array
    {
        return [
            ['Active', 'badge-success'],
            ['Absent', 'badge-danger'],
            ['Late', 'badge-warning'],
            ['Excused', 'badge-info'],
            ['Inactive', 'badge-secondary'],
            // Unmapped statuses default to secondary.
            ['Unknown', 'badge-secondary'],
        ];
    }

    public function testTruncateTextLeavesShortTextUnchanged(): void
    {
        $this->assertSame('short text', truncateText('short text', 50));
    }

    public function testTruncateTextTruncatesLongText(): void
    {
        $text = str_repeat('a', 60);

        $result = truncateText($text, 10);

        $this->assertSame(str_repeat('a', 10) . '...', $result);
    }

    public function testTruncateTextRespectsDefaultLength(): void
    {
        $text = str_repeat('b', 60);

        $result = truncateText($text);

        $this->assertSame(str_repeat('b', 50) . '...', $result);
    }

    public function testGetAvatarColorReturnsColorFromPalette(): void
    {
        $palette = ['#003d7a', '#0056b3', '#007bff', '#17a2b8', '#20c997'];

        $this->assertContains(getAvatarColor('Alice'), $palette);
    }

    public function testGetAvatarColorIsDeterministic(): void
    {
        $this->assertSame(getAvatarColor('Deslin'), getAvatarColor('Different'));
        // Same first character => same color.
        $this->assertSame(getAvatarColor('Ann'), getAvatarColor('Andrew'));
    }

    public function testCheckPermissionMatchesSessionRole(): void
    {
        $_SESSION['user_role'] = 'admin';

        $this->assertTrue(checkPermission('admin'));
        $this->assertFalse(checkPermission('teacher'));
    }

    public function testCheckPermissionReturnsFalseWithoutRole(): void
    {
        $this->assertFalse(checkPermission('admin'));
    }

    public function testCheckAnyPermission(): void
    {
        $_SESSION['user_role'] = 'accountant';

        $this->assertTrue(checkAnyPermission(['admin', 'accountant']));
        $this->assertFalse(checkAnyPermission(['admin', 'teacher']));
    }

    public function testCheckAnyPermissionReturnsFalseWithoutRole(): void
    {
        $this->assertFalse(checkAnyPermission(['admin', 'teacher']));
    }
}
