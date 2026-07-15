<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Grade::calculateGrade().
 *
 * The Grade constructor opens a database connection, so the instance is
 * created without invoking the constructor (via reflection) to isolate the
 * pure grade-calculation logic under test.
 */
class GradeTest extends TestCase
{
    private Grade $grade;

    protected function setUp(): void
    {
        $this->grade = (new ReflectionClass(Grade::class))->newInstanceWithoutConstructor();
    }

    /**
     * @dataProvider gradeProvider
     */
    public function testCalculateGrade(float $score, string $letter, float $point): void
    {
        $result = $this->grade->calculateGrade($score);

        $this->assertSame($letter, $result['letter']);
        $this->assertSame($point, $result['point']);
    }

    public static function gradeProvider(): array
    {
        return [
            'A at boundary'     => [90.0, 'A', 4.0],
            'A above boundary'  => [100.0, 'A', 4.0],
            'B at boundary'     => [80.0, 'B', 3.0],
            'B mid range'       => [85.0, 'B', 3.0],
            'C at boundary'     => [70.0, 'C', 2.0],
            'D at boundary'     => [60.0, 'D', 1.0],
            'F below boundary'  => [59.99, 'F', 0.0],
            'F at zero'         => [0.0, 'F', 0.0],
        ];
    }

    public function testCalculateGradeReturnsExpectedKeys(): void
    {
        $result = $this->grade->calculateGrade(75);

        $this->assertArrayHasKey('letter', $result);
        $this->assertArrayHasKey('point', $result);
    }
}
